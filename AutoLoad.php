<?php

/**
 * Class AutoLoad helper
 *
 * @author Evgeniy Dubskiy
 */
class AutoLoad
{
    /**
     *	Default PEAR extension for class file
     */
    const PEAR_EXT = "php";

    /**
     *	Delimiter by PEAR agreement. Example: Machine_Robo (possible class location -> Machine/Robo.php)
     */
    const PEAR_BAR = "_";

    /**
     *	Additionally look for Class in subfolder by class name prefix.
     *	Example: If class name = NNN_MMM => subfolder path = NNN/MMM
     */
    const LOOK_SUBFOLDER_BY_PEAR_BAR  	= true;

    /**
     *	Additionally look for Class in folder named as a Class name
     *	Example: If class name = NNN_MMM => subfolder path = NNN_MMM/NNN_MMM
     */
    const LOOK_SUBFOLDER_BY_CLASS_NAME 	= true;

    /**
     *	Using different functions and methods which to help to find class
     */
    const USE_CLASS_AUTO_FIND 		   	= true;

    /**
     *	Do we use absolut path for base path?
     *	Example: if Autoload is located in /home/onefunnydude/Sites/Sync_Scripts/libs/Autoload.php => BaseIncludePath:
     *	1. ABSOLUT  -> /home/onefunnydude/Sites/Sync_Scripts/libs
     *	2. RELATIVE -> libs/
     */
    const USE_ABSOLUT_PATH			  	= true;

    /**
     *	Already Registered Classes

    public static $classesRegistered = array();

    /**
     *	Currently Added functions
     */
    public static $funcsAdded 	= array();

    /**
     *	Possible class Paths to Look At
     */
    public static $lookUpPaths 	= array();

    /**
     *	Possible function names to go for Class Name to find
     */
    private static $lookUpFuncs = array ();

    /**
     *	Base Path
     */
    private static $basePath;

    /**
     *	Registered classes storage
     */
    private static $classesRegistered;

    /**
     *	Sets Lookup Paths for Class Search
     */
    public static function SetLookUpPaths($paths, $replaceDefault = false)
    {
        if ($replaceDefault)
        {
            self::$lookUpPaths = $paths;
        }
        elseif (is_array($paths))
        {
            foreach ($paths as $path)
            {
                array_unshift(self::$lookUpPaths, $path);
            }
        }
        elseif($paths)
        {
            array_unshift(self::$lookUpPaths, $paths);
        }
        return true;
    }

    /**
     *	Sets Lookup Ways (functions) for Class Search
     */
    public static function SetLookUpWays($ways, $replaceDefault = false)
    {
        if ($replaceDefault)
        {
            self::$lookUpFuncs = $ways;
        }
        elseif (is_array($ways))
        {
            foreach ($ways as $func)
            {
                array_unshift(self::$lookUpFuncs, $func);
            }
        }
        elseif($ways)
        {
            array_unshift(self::$lookUpFuncs, $ways);
        }
        return true;
    }

    /**
     *	Adds class name or list of class names to the global list of classes which should be loaded
     *	@param $className - array of string which contains class name(s)
     *	@return bool
     */
    public static function Add($className)
    {
        if (is_array($className))
        {
            foreach($className as $curClassName)
            {
                self::$funcsAdded[] = $curClassName;
            }
        }
        else
        {
            self::$funcsAdded[] = $className;
        }
        return true;
    }

    /**
     *	Tries to Load class with the help of every added function
     */
    public static function Create($className)
    {
        #echo $classesRegistered[$className];
        $classRegistered = isset(self::$classesRegistered[$className]) ? true : false;

        if ($classRegistered OR class_exists($className))
        {
            return true;
        }

        if ( ! is_array(self::$funcsAdded))
        {
            return false;
        }

        foreach (self::$funcsAdded as $func)
        {
            #echo "Calling Func {$func} with arg {$className} <HR>";

            #if (function_exists())	@todo

            if (call_user_func($func, $className))
            {
                self::$classesRegistered[$className] = true;
                return true;
            }
        }
        return false;
    }

    public static function GetPathWithEndSlash($path)
    {
        if (empty($path))
        {
            return $path;
        }
        $path .= substr($path, strlen($path) -1 , 1) != "/" ? "/" : "";

        return $path;
    }

    /**
     * Tries to include class with given way functions of single path
     *
     * @param string $fileName - path to file
     * @param string [$filePath] - name of file
     * @return string|bool
     */
    public static function TryIncludeClass($fileName, $filePath = "")
    {
        if (self::NonExistsBaseIncludePath())
        {
            self::SetBaseIncludePath();
        }

        # 1. Look class inside filePath with given fileName
        # Example:
        # filePath =  /home/onefunnydude/Sites/Sync_Scripts/libs/ ;
        # fileName = PaySystem_Yandex ;
        # Result   = /home/onefunnydude/Sites/Sync_Scripts/libs/PaySystem_Yandex.php
        $curFile	= self::GetResultFilePath($filePath, $fileName);

        self::PrintReport($filePath, $fileName, $curFile);

        if (file_exists($curFile))
        {
            include_once($curFile);
            return $curFile;
        }

        # 2. Look class inside subfolder by fileName prefix.
        # Example:
        # filePath =  /home/onefunnydude/Sites/Sync_Scripts/libs/
        # fileName = PaySystem_Yandex => = PaySystem/Yandex
        # Result   = /home/onefunnydude/Sites/Sync_Scripts/libs/PaySystem/Yandex.php
        if (self::LOOK_SUBFOLDER_BY_PEAR_BAR)
        {
            $subFolderPath = self::GetPathByClassName($fileName);

            if ($subFolderPath != $fileName)
            {
                $curFile = self::GetResultFilePath($filePath, $subFolderPath);

                self::PrintReport($filePath, $subFolderPath, $curFile);

                if (file_exists($curFile))
                {
                    include_once($curFile);
                    return $curFile;
                }
            }
        }

        # 3. Look class inside subfolder by fileName prefix.
        # Example:
        # filePath =  /home/onefunnydude/Sites/Sync_Scripts/libs/
        # fileName = Machine
        # Result   = /home/onefunnydude/Sites/Sync_Scripts/libs/Machine/Machine.php
        if (self::LOOK_SUBFOLDER_BY_CLASS_NAME)
        {
            $subFolderPath = $fileName . "/" . $fileName;
            #$curFile = self::$basePath . $filePath .  $subFolderPath .  "." . self::PEAR_EXT;
            $curFile = self::GetResultFilePath($filePath, $subFolderPath);

            self::PrintReport($filePath, $subFolderPath, $curFile);

            if (file_exists($curFile))
            {
                include_once($curFile);
                return $curFile;
            }
        }

        # 4. Using different autofind functions to find class
        # Example:
        # filePath =  /home/onefunnydude/Sites/Sync_Scripts/libs/
        # func = strtoupper()
        # filePath = PaySystem_Yandex (itself) => PAYSYSTEM_YANDEX  And PAYSTEM/YANDEX (by subfolder prefix)
        # Result   = /home/onefunnydude/Sites/Sync_Scripts/libs/PAYSYSTEM_YANDEX.php
        # AND
        # Result (if const LOOK_SUBFOLDER_BY_PEAR_BAR set)  = /home/onefunnydude/Sites/Sync_Scripts/libs/PAYSTEM/YANDEX.php
        if (self::USE_CLASS_AUTO_FIND)
        {
            if (is_array(self::$lookUpFuncs))
            {
                foreach (self::$lookUpFuncs as $func)
                {
                    if (function_exists($func))
                    {
                        $newFileName = $func($fileName);
                        $curFile = self::GetResultFilePath($filePath, $newFileName);

                        if (file_exists($curFile))
                        {
                            include_once($curFile);
                            return $curFile;
                        }

                        if (self::LOOK_SUBFOLDER_BY_PEAR_BAR)
                        {
                            $subFolderPath = self::GetPathByClassName($newFileName);
                            $curFile 	   = self::GetResultFilePath($filePath, $subFolderPath);

                            if (file_exists($curFile))
                            {
                                include_once($curFile);
                                return $curFile;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    private static function NonExistsBaseIncludePath()
    {
        return empty(self::$basePath) ? true : false;
    }

    public static function GetPathByClassName($className, $useAbsolutPath = false)
    {
        if (empty($className))
        {
            return false;
        }

        $filePath = str_replace(self::PEAR_BAR, "/", $className);

        return $filePath;
    }

    /**
     * 	Yehh.. It all begins with BaseIncludeDirectory :)
     *	2 ways of setting BaseIncludePath :
     *	1. Set manually.
     *		a) RELATIVE: Example SetBaseIncludePath("libraries").
     *			Script launch path (!NOT AutoLoader path!) = /Test_Scripts => BaseIncludeDirectory = libraries/ (parent folder = Test_Scripts)
     *			So, if you launch script Test_Scripts/daily.php, BaseIncludeDirectory = libraries/ ( == Test_Scripts/libraries/)
     *		b) ABSOLUT : Example SetBaseIncludePath("home/onefunnydude/Sites/Test_Scripts/libraries")
     *			Your result BaseIncludeDirectory = home/onefunnydude/Sites/Test_Scripts/libraries
     *	2. Set Automatically (2 ways depending on const USE_ABSOLUT_PATH)
     *		a) RELATIVE: Example SetBaseIncludePath()
     *			AutoLoader launch path  ( ! NOT Script launch path ! ) = home/onefunnydude/Sites/Test_Scripts/libs => BaseIncludeDirectory = libs/
     *		b) ABSOLUT: Example SetBaseIncludePath()
     *			AutoLoader launch path  ( ! NOT Script launch path ! ) = Test_Scripts/libs => BaseIncludeDirectory = home/onefunnydude/Sites/Test_Scripts/libs/
     */
    public static function SetBaseIncludePath($path = false)
    {
        if ( ! empty($path))
        {
            self::$basePath = str_replace("\\", "/", $path);
            return true;
        }

        $currentPath = dirname(__FILE__);

        # Example:
        # ABSOLUT PATH  = /home/onefunnydude/Sites/Sync_Scripts/libs/
        # RELATIVE PATH = libs/
        self::$basePath = self::USE_ABSOLUT_PATH ? $currentPath : basename($currentPath);
        self::$basePath = self::GetPathWithEndSlash(self::$basePath);

        return true;
    }

    private static function GetResultFilePath()
    {
        $resultPath = self::GetPathWithEndSlash(self::$basePath);
        $ext		= self::PEAR_EXT;
        $pathParts  = func_get_args();

        if ( ! empty($pathParts))
        {
            foreach ($pathParts as $pathPart)
            {
                $pathPart 	 = self::GetPathWithEndSlash($pathPart);
                $resultPath .= $pathPart;
            }
            # Cutting last slash
            $resultPath = substr($resultPath, 0, -1);
        }

        $resultPath .= "." . $ext;

        return $resultPath;
    }

    private static function PrintReport($filePath, $fileName, $curFile)
    {
        if (isset($_GET['print']))
        {
            $dataToPrint = get_defined_vars();
            $endLine = "<BR>";

            if ( ! self::NonExistsBaseIncludePath())
            {
                echo "Base path = " . self::$basePath . $endLine;
            }

            if (is_array($dataToPrint))
            {
                foreach($dataToPrint as $varName => $varValue)
                {
                    echo $varName . " = " . $varValue . $endLine;
                }
                echo $endLine;
            }
        }
    }
}

function __autoload($className)
{
    AutoLoad::Create($className);
}

/**
 * Loads when the file loads and tries to find class within given default paths
 *
 * @param string $className Class Name
 * @return bool
 */
function DefaultLookupPaths($className)
{
    if (AutoLoad::TryIncludeClass($className))
    {
        return true;
    }

    if (AutoLoad::$lookUpPaths)
    {
        foreach (AutoLoad::$lookUpPaths as $path)
        {
            if (AutoLoad::TryIncludeClass($className, $path))
            {
                return true;
                break;
            }
        }
    }
    return false;
}

AutoLoad::Add("DefaultLookupPaths");