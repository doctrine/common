<?php

namespace MyCompany\Annotations {
    /**
     * @Annotation
     */
    class Foo 
    {
        public $bar;
    }

    /**
     * @Annotation
     */
    class Bar
    {
        public $foo;
    }
}

namespace Doctrine\Tests\Common\Annotations\Fixtures {


    /**
     * @MyCompany\Annotations\Foo(bar="foo")
     * @MyCompany\Annotations\Bar(foo="bar")
     * @Foo\Bar
     * @Foo\Bar\Baz
     */
    class IgnoreAnnotationWithWildcard
    {

    }
}