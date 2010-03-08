### Write Tests

The first key to good unit tests is actually writing them. Very often we find
ourselves rushing out a new functionality without writing tests, be it because
of lack of time, motivation or whatsoever. It is a proven fact though that
finding and fixing bugs is much more expensive than writing proper unit tests!
Whether you write tests before the implementation (TDD style) or after the
implementation: Write them.

### Test Frequently

Once you have a decent suite of unit tests, it is important to run them
frequently. If they are fast, you can run them after every change you make to
the code. If they take more time, test at least once before committing your
code to the shared code base. The less time passes between introducing an
error and noticing it, the less time it takes to fix it. Lime features a
command line utility that makes running tests or suites of tests very easy.

### Performance

Running your tests frequently is not possible if your test suite takes 30
minutes to run. Therefore, make sure your tests are as fast as possible.
I am not talking about pseudo optimizations like replacing double by single
quotes, but about eliminating slow test dependencies like databases, the
file system etc. In the second chapter we will hear about how multiprocessing
in Lime can further help you to speed up your testing process.

### Reliability

The purpose of every test is to inform you about a specific error in your
program - but only if that error exists. Nothing is worse than tests that
randomly fail or those that point out a problem in the wrong part of your
code. Therefore, when you have written a test, try to break the functionality
it is meant to test and verify whether the test fails. In the third chapter
you will learn how Lime's annotations help you to isolate your tests from each
other.

### Readability

The last important factor of successful unit testing is frequently overlooked:
Make sure your tests are short and readable. If it is easy to find out what a
test is testing, it is easy to discover why it fails. Short and readable tests
also serve as documentation. They show what your code can do and how it is used.
Long tests usually are a sign for either bad test organization (*Test Smells*)
or a bad architecture (*Code Smells*). In either case, you need to reorganize
(*refactor*) your code. Lime supports readable tests by offering a very clean
and concise API.




Creation methods