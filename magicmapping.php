<?php
// Copyright (c) 2014 Robin Bailey @ Dionach Ltd.

$classlist = get_declared_classes();

// The methods we're interested in
$magic = array("__wakeup", "__destruct", "__toString", "__get", "__set", "__call");

foreach($classlist as $class)
{
    $reflClass = new ReflectionClass($class);

    // Ignore classes from PHP core/extensions
    if ($reflClass->isUserDefined())
    {
        foreach($magic as $method)
        {
            try
            {
                if ($reflClass->getMethod($method))
                {
                    $reflMethod = new ReflectionMethod($class, $method);
                    $parent = $reflMethod->getDeclaringClass()->getName();
                    $filename = $reflMethod->getDeclaringClass()->getFileName();
                    $startline = $reflMethod->getStartLine();

                    // If filename is not defined the class inherits from a core/extension class
                    if ($filename)
                    {
                        // Get the source code of the method
                        $exp = $reflMethod->export($class, $method, 1);

                        // Extract the filename, start and end line numbers
                        preg_match("/@@\s(.*)\s(\d+)\s-\s(\d+)/i", $exp, $matches);
                        $source = file($filename);

                        // -1/+1 to include the first and last lines, incase code is on same line as method declaration
                        $functionBody = implode("", array_slice($source, $matches[2] - 1, ($matches[3]-$matches[2] + 1)));

                        // Check for interesting function calls
                        if (preg_match("/eval|assert|call_user_func|system|popen|shell_exec|include|require|file_get_contents|unlink/",
                                       $functionBody, $m))
                        {
                            $interesting = $m[0];
                        }

                        print $class . "::" . $method . "() ";
                        if ($parent !== $class)
                        {
                            print "[extends " . $parent . "] ";
                        }
                        if (isset($interesting))
                        {
                            print "{calls " . $interesting . "} ";
                            unset($interesting);
                        }

                        print "- " . $filename . ':' . $startline . "\n";
                    }
                }
            }
            catch (Exception $e) {}
        }
    }
}
exit;
