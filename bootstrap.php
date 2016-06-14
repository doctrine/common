<?php

\Doctrine\Common\Enum\Factory::registerEngine(new \Doctrine\Common\Enum\Engine\SPL());
\Doctrine\Common\Enum\Factory::registerEngine(new \Doctrine\Common\Enum\Engine\MyCLabs());
