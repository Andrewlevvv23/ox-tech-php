<?php

namespace App\Telegram\Bot;

use Exception;

class Factory
{
    protected Message $message;
    protected File $file;

    public function __construct()
    {
        $this->message = new Message();
        $this->file = new File();
    }

    /**
     * @throws Exception
     */
    public function __call(string $name, array $arguments)
    {
        foreach ($this as $prop)
            if (method_exists($prop, $name))
                return call_user_func_array([$prop, $name], $arguments);

        throw new Exception('This method do not existing: ' . $name);
    }
}
