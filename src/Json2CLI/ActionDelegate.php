<?php

namespace Json2CLI;

use Utopia\CLI\Console;

class ActionDelegate
{
    public function handleAction(
        string $runtime,
        string $actionClass,
        string $functionName,
        array $params,
    )
    {
        switch ($runtime) {
            case Runtimes::PHP:
                self::handlePHPAction($actionClass, $functionName, $params);
                break;
            case Runtimes::NODE:
                self::handleNodeAction($actionClass, $functionName, $params);
                break;
            case Runtimes::RUBY:
                self::handleRubyAction($actionClass, $functionName, $params);
                break;
            case Runtimes::DOTNET:
                self::handleDotNetAction($actionClass, $functionName, $params);
                break;
        }
    }

    private static function handlePHPAction(
        string $actionClass,
        string $functionName,
        array $params,
    )
    {
        $action = new $actionClass();
        $action->$functionName(...$params);
    }

    private static function handleNodeAction(
        string $actionClass,
        string $functionName,
        array $params,
    )
    {
        $stdin = '';
        $stdout = '';
        $stderr = '';
        $cmd = "node -e 'require(\"./$actionClass\").$functionName(\"" . join('", "', $params) . "\");'";
        $code = Console::execute($cmd, $stdin, $stdout, $stderr);
        if ($code != 0) {
            Console::error("Failed running node action: $stdout\n$stderr") and die;
        }
        Console::info($stdout);
    }

    private static function handleRubyAction(
        string $actionClass,
        string $functionName,
        array $params,
    )
    {
        $stdin = '';
        $stdout = '';
        $stderr = '';
        $cmd = "ruby -r './$actionClass' -e '$functionName(\"" . join('", "', $params) . "\")'";
        $code = Console::execute($cmd, $stdin, $stdout, $stderr);
        if ($code != 0) {
            Console::error("Failed running node action: $stdout\n$stderr") and die;
        }
        Console::info($stdout);
    }

    private static function handleDotNetAction(
        string $actionClass,
        string $functionName,
        array $params,
    )
    {
        $stdin = '';
        $stdout = '';
        $stderr = '';
        $paramString = '';
        $paramsValues = '';

        $index = 0;
        foreach ($params as $paramName => $param) {
            $paramString .= "$paramName: Args[$index]";
            if ($index != count($params) - 1) {
                $paramString .= ', ';
            }
            $paramsValues .= "$param ";
            $index++;
        }

        $cmd = "echo \"$(cat $actionClass)\n$functionName($paramString);\" > tmp.csx && csi tmp.csx $paramsValues && rm tmp.csx";
        $code = Console::execute($cmd, $stdin, $stdout, $stderr);
        if ($code != 0) {
            Console::error("Failed running node action: $stdout\n$stderr") and die;
        }
        Console::info($stdout);
    }
}