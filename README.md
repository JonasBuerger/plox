# plox
Following along with chapters 1 - 13 of the "Crafting Interpreters" book.
Implements the first lox interpreter in php instead of java.

Implemented in 3036 Lines, calculated with:
````shell
find ./src -type f -exec wc -l {} +
````

The Benchmark at the start of chapter 14 runs way too long. After reducing the test to fib(25) it takes 64 seconds:

````
$ bin/run example/ch2_benchmark.lox
75025
63.920700073242
````
