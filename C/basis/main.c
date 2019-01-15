#include <stdio.h>
#include "func.h"
#define LEN 5
int container[LEN] = {10, 5, 2, 4, 7};

int max(int x, int y); // 函数的声明，分号不能省略

/**
 * （1）初始：保证在初始的时候不变式为真。
 * （2）保持：保证在每次循环开始和结束的时候不变式都为真。
 * （3）终止：如果程序可以在某种条件下终止，那么在终止的时候，就可以得到自己想要的正确结果
 */
void insertion_sort(void) {
    int i, j, current;
    for (i = 1; i < LEN; i++) {
        current = container[i + 1];
        for (j = i - 1; j >= 0 && container[j] > current; j--) {
            printf("inner j is %d\n", j);
            container[j + 1] = container[j];
        }
        printf("j is %d\n", j);
        container[j + 1] = current;
    }
    printf("%d, %d, %d, %d, %d\n",
           container[0],
           container[1],
           container[2],
           container[3],
           container[4]);
}

int main() {
    bubbling();
    return 0;
}