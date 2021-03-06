//  How to access GPIO registers from C-code on the Raspberry-Pi
//  Example program
//  15-January-2012
//  Dom and Gert
//


// Access from ARM Running Linux

#define BCM2708_PERI_BASE        0x20000000
#define GPIO_BASE                (BCM2708_PERI_BASE + 0x200000) /* GPIO controller */


#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <dirent.h>
#include <fcntl.h>
#include <assert.h>
#include <unistd.h>
#include <sys/mman.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <bcm2835.h>
#include <unistd.h>
#include <time.h>

#define MAXTIMINGS 100

//#define DEBUG

#define DHT11 11
#define DHT22 22
#define AM2302 22

int readDHT(int type, int pin);

int main(int argc, char **argv)
{
   int i;

  if (!bcm2835_init())
        return 1;

  if (argc != 3) {
	printf("usage: %s [11|22|2302] GPIOpin#\n", argv[0]);
	printf("example: %s 2302 4 - Read from an AM2302 connected to GPIO #4\n", argv[0]);
	return 2;
  }
  int type = 0;
  if (strcmp(argv[1], "11") == 0) type = DHT11;
  if (strcmp(argv[1], "22") == 0) type = DHT22;
  if (strcmp(argv[1], "2302") == 0) type = AM2302;
  if (type == 0) {
	printf("Select 11, 22, 2302 as type!\n");
	return 3;
  }
  
  int dhtpin = atoi(argv[2]);

  if (dhtpin <= 0) {
	printf("Please select a valid GPIO pin #\n");
	return 3;
  }


  for(i = 0; i < 11; i++){
      printf("[%d]\n", i);
      readDHT(type, dhtpin);
      sleep(5);
  }

   
  //while(!readDHT(type, dhtpin));
  
  return 0;

} // main

int readDHT(int type, int pin) {
  int counter = 0;
  int laststate = HIGH;
  int j=0;
  FILE *fp, *fxml;
  time_t t1 = time(NULL);
  struct tm *nPtr = localtime(&t1);
  char time[20];
  int bits[250], data[100];
  int bitidx = 0;

  strftime(time, 20, "%Y/%m/%d %X", nPtr);
  memset(bits, 0, 250);
  memset(data, 0, 100);

  // Set GPIO pin to output
  bcm2835_gpio_fsel(pin, BCM2835_GPIO_FSEL_OUTP);

  bcm2835_gpio_write(pin, HIGH);
  usleep(500000);  // 500 ms
  bcm2835_gpio_write(pin, LOW);
  usleep(20000);

  bcm2835_gpio_fsel(pin, BCM2835_GPIO_FSEL_INPT);

  data[0] = data[1] = data[2] = data[3] = data[4] = 0;

  // wait for pin to drop?
  while (bcm2835_gpio_lev(pin) == 1) {
    usleep(1);
  }

  // read data!
  for (int i=0; i< MAXTIMINGS; i++) {
    counter = 0;
    while ( bcm2835_gpio_lev(pin) == laststate) {
	counter++;
	//nanosleep(1);		// overclocking might change this?
        if (counter == 1000)
	  break;
    }
    laststate = bcm2835_gpio_lev(pin);
    if (counter == 1000) break;
    bits[bitidx++] = counter;

    if ((i>3) && (i%2 == 0)) {
      // shove each bit into the storage bytes
      data[j/8] <<= 1;
      if (counter > 200)
        data[j/8] |= 1;
      j++;
    }
  }


#ifdef DEBUG
  for (int i=3; i<bitidx; i+=2) {
    printf("bit %d: %d\n", i-3, bits[i]);
    printf("bit %d: %d (%d)\n", i-2, bits[i+1], bits[i+1] > 200);
  }
#endif

  printf("Data (%d): 0x%x 0x%x 0x%x 0x%x 0x%x\n", j, data[0], data[1], data[2], data[3], data[4]);

  if ((j >= 39) &&
      (data[4] == ((data[0] + data[1] + data[2] + data[3]) & 0xFF)) ) {
     // yay!
     
    fp = fopen("/home/web/www/DHT22/docs/temp.txt", "w");
    fxml = fopen("/home/web/www/DHT22/xml/temp.xml", "w");
     
     if (type == DHT11)
	printf("Temp = %d *C, Hum = %d \%\n", data[2], data[0]);
     if (type == DHT22) {
	float f, h;
	h = data[0] * 256 + data[1];
	h /= 10;

	f = (data[2] & 0x7F)* 256 + data[3];
        f /= 10.0;
        if (data[2] & 0x80)  f *= -1;
        printf("%s %.1f %.1f\n", time, f, h);

        // temp.txt
        fprintf(fp, "%s %.1f %.1f\n", time, f, h);

        // temp.xml
        fprintf(fxml, "<CATALOG>\n\t<DATE>%s</DATE>\n\t<TEMP>%.1f</TEMP>\n\t<HUD>%.1f</HUD>\n</CATALOG>", time, f, h);
        
        fclose(fp);
        fclose(fxml);
     }
    return 1;
  }

  return 0;
}
