<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

use Symfony\Component\Finder\SplFileInfo;

class FileUnavailableException extends \Exception
{
    private SplFileInfo $unavailableFile;

    public function getUnavailableFile(): SplFileInfo
    {
        return $this->unavailableFile;
    }

    public function setUnavailableFile(SplFileInfo $unavailableFile): self
    {
        $this->unavailableFile = $unavailableFile;

        return $this;
    }
}
