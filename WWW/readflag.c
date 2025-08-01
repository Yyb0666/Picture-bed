#include <stdio.h>
#include <stdlib.h>

int main() {
    FILE *fp = fopen("/flag", "r");
    if (fp == NULL) {
        perror("flag open failed");
        return 1;
    }
    char flag[128];
    fgets(flag, sizeof(flag), fp);
    fclose(fp);
    printf("%s\n", flag);
    return 0;
}
