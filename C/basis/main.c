#include <stdio.h>
#include "stack.h"


int a, b = 1;


int main(void)
{
    push('a');
    push('b');
    push('c');

    while(!is_empty())
        putchar(pop());
    putchar('\n');

    return 0;
}