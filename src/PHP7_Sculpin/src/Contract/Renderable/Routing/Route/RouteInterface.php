<?php

declare(strict_types=1);

/*
 * This file is part of Symplify
 * Copyright (c) 2016 Tomas Votruba (http://tomasvotruba.cz).
 */

namespace Symplify\PHP7_Sculpin\Contract\Renderable\Routing\Route;

use Symplify\PHP7_Sculpin\Renderable\File\AbstractFile;

interface RouteInterface
{
    public function matches(AbstractFile $file) : bool;

    public function buildOutputPath(AbstractFile $file) : string;

    public function buildRelativeUrl(AbstractFile $file) : string;
}
