#include <stdio.h>

int main() {
    int foo = 5;
    int bar = 10;
    int *foo_pointer;

    printf("&foo %p\n", &foo);
    printf("&bar %p\n", &bar);
    printf("&foo_pointer %p\n", &foo_pointer);
    printf("foo_pointer %p\n", foo_pointer);
    printf("foo %d\n", foo);

    foo_pointer = &foo;
    printf("foo_pointer %p\n", foo_pointer);
    printf("&foo_pointer %p\n", &foo_pointer);

    *foo_pointer = 1;

    printf("foo %d\n", foo);

    return 0;
}