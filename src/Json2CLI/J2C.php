<?php

namespace Json2CLI;

use Utopia\CLI\CLI;

class J2C
{
    const COMMAND_RUNTIME = "runtime";
    const COMMAND_ACTION_FILE = "codePath";
    const COMMAND_ACTION_FUNCTION_NAME = "function";
    const COMMAND_PARAMS_KEY = "parameters";
    const PARAM_VALIDATOR_KEY = "validator";
    const PARAM_VALIDATOR_PARAMS_KEY = "parameters";
    const PARAM_VALIDATOR_TYPE_KEY = "type";
    const PARAM_DEFAULT_KEY = "default";
    const PARAM_DESC_KEY = "desc";
    const PARAM_OPTIONAL_KEY = "optional";

    public function createFromFile(string $path): CLI
    {
        return $this->createFromJson(file_get_contents($path));
    }

    public function createFromJSON(string $json): CLI
    {
        $cli = new CLI();
        $commands = json_decode($json, true);
        foreach ($commands as $commandName => $command) {
            $task = $cli->task($commandName);

            foreach ($command[J2C::COMMAND_PARAMS_KEY] as $paramName => $param) {
                $validator = $param[J2C::PARAM_VALIDATOR_KEY];
                $validator = array_key_exists(J2C::PARAM_VALIDATOR_PARAMS_KEY, $validator)
                    ? new $validator[J2C::PARAM_VALIDATOR_TYPE_KEY](...$validator[J2C::PARAM_VALIDATOR_PARAMS_KEY])
                    : new $validator[J2C::PARAM_VALIDATOR_TYPE_KEY]();

                $task->param(
                    $paramName,
                    $param[J2C::PARAM_DEFAULT_KEY],
                    $validator,
                    $param[J2C::PARAM_DESC_KEY],
                    $param[J2C::PARAM_OPTIONAL_KEY]
                );
            }

            $actionClass = $command[J2C::COMMAND_ACTION_FILE];
            $functionName = $command[J2c::COMMAND_ACTION_FUNCTION_NAME];
            $task->action(function (...$params) use ($command, $functionName, $actionClass) {
                $delegate = new ActionDelegate();
                $delegate->handleAction(
                    $command[J2C::COMMAND_RUNTIME],
                    $actionClass,
                    $functionName,
                    $params
                );
            });
        }
        return $cli;
    }
}