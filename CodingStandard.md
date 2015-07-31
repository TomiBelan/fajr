# I wanna clean up my mess.

# Introduction #

Coding standard is an important part of coding practice. It is something like a common language for developers. If you have tried to edit some code with random naming of variables, wrong indentation and without doccomments, you know what I'm talking about.

TODO: how to test for coding style violations.

# Details #

## Formatting ##
### Line length ###
  * Each line of text should be at most **80 characters** long. An exception to this rule are class string constants, which cannot be split, and namespace import statements.
### Encoding ###
  * Each file should be in **UTF-8** format. Exceptions to this rule are test data files which should contain the original encoding of the source.
### Indentation ###
  * use **spaces, not tabs**
  * **indent** nested scope by **2 spaces**

### Opening brace "{" ###
  * for classes, functions on next line
  * for inline structures (if, while, switch) on the line prepended by exactly one space.

Example:
```
class Test
{
  function doIt()
  {
    if (1 == 2) {
      assert(false);
    }
  }
}
```

### Conditionals ###
Example:
```
  if (condition) {
    // do something
  } else {
    // do something else
  }
  
  if ((very_long) &&
      (condition)) {
    // do something
  }
```

### Iterating over arrays ###
  * Prefer **foreach** statement instead of **for($i = 0; $i < 2; $i++)**

### Function calls and operators ###
  * Separate function arguments with exactly **one space**.
  * Use one **space before and after** each **operator** (+ - `*` / =).
  * If the function arguments are too big to fit into one line, divide the arguments into lines and indent them as the first argument. (In some cases, where the arguments are extremely long, it may make sense to indent only with 4 spaces).

Example:
```
  $result = call($arg1, $arg2, $arg3);
  $result = ($a + $b) / $c;
  $result -= 10;
  $string = "String" . "concatenation";
  $longCall = long_call($long_arg_1, $long_arg_2,
                        $long_arg_3);

```

## Documentation comments ##
### File doc comments ###
  * Each file should also contain a file doccomment, which contains **description and @copyright, @package, @subpackage, @author and @filesource annotations**
  * arguments of @annotations should be aligned
  * @package: Fajr
  * @subpackage: Path to the file, ommitting {src,tests} at the beginning. We replace directory separator "/" with double underscore "`__`" and start each token with an uppercase character.

Example (for file src/libfajr/my\_directory/MyClass.php):
```
/**
 * This file contains the super-mega-cool spying component.
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Libfajr__My_directory
 * @author     James Bond <james@bond.com>
 * @filesource
 */
```

Years in the above copyright statement should be written as comma separated list, i.e.
Copyright (c) 2010, 2011 ...

### Class doc comments ###
  * Each class should contain a description, @author, @package and @subpackage annotations. Optionally, it may contain a long description with examples of use etc.

### Member doc comments ###
  * Each member of a class shoud contain a **@var** annotation with its type and purpose. You may use one-line doccomments.

Example:
```
  /** @var int counter of requests. */
  public $numQueries;
```

### Function doc domments ###
  * Each function should have a doccomment with the **description** of its purpose, **@param** annotation for each parameter and **@returns** (Note: do not use @return) annotation for the return type. Block of @params and @returns should be delimited with an empty line. TODO(align of @params).
Example:
```
  /**
   * A very cool function making some magic stuff.
   *
   * Optional long description
   * spanning several lines.
   * 
   * @param double $amount Amount of magic we need
   * @param string $type    Type of magic to make
   *
   * @returns MyMagicClass magic created from ingredients
   */
   public function createMagic($amount, $type)
```

### Type hinting ###
  * Doccomments are really good, but when you can, ensure that **parameters** are **strongly typed** (works only for arrays and classes). This helps especially when you forget to import a class from other namespace.

Example:
```
  public function doStuff($strArg, $intArg, MyClass $classArg);
```

## Naming conventions ##

### Files & directories ###
  * Source files should reside under /src, testfiles under /tests.
  * Test files should end with "Test.php".
  * Please keep both directory trees in sync.
  * Directories and files should contain only leters, numbers and underscores. Directories can contain only small letters.
  * File names should be the same as the class defined in them.

Example:
```
src/libfajr/my_directory/MyClass.php
src/libfajr/my_directory/no_class.php
tests/libfajr/my_directory/MyClassTest.php
```

### Namespaces ###
  * Namespaces should start with "fajr" and copy the directory structure ommitting {src,tests}.
  * "namespace" keyword must appear right after the file doccomment.
  * the "use" keyword must apper right after the namespace keyword and must be separated by one empty line.
  * You must import ("use" keyword) all classes outside of this namespace which you intend to use. This also holds if you want the classname to be used only in doccomment (so phpdoc can pick up the information).
  * Do not forget to input global classes like "Exception" if you use them.
  * when importing with "use", do not prepend namespace with "\"
  * order of imports must be sorted. Best way to do this in vim is ":!sort".
  * Import only classes, do not import namespaces (i.e. do not use "use path\to\directory" and then "$tmp = new directory\MyClass()".

Example (for file src/libfajr/my\_directory/MyClass):
```
/**
 * file doccomment
 */

namespace fajr\libfajr\my_directory;

use Exception;
use fajr\libfajr\other_dir\OtherClass;
```


### Classes ###
  * Classes should be in CamelCapsStyle and contain only letters and digits.

### Variables and functions ###
  * Class function names should be in smallCamelCapsStyle()
  * Class variable names should be in $smallCamelCapsStyle
  * TODO: private and protected members and functions should start
> > with underscore.
  * Constants names should by in CONSTANT\_STYLE.

Example:
```
class MyClass
{
  public $data;

  public function doSomething($x)
  {
    return $x . $data;
  }
}
```