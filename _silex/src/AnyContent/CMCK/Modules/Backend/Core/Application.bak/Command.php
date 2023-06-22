<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Application;

abstract class Command extends \Knp\Command\Command
{

    const escapeBlack = "\033[0;30m";
    const escapeRed = "\033[0;31m";
    const escapeGreen = "\033[0;32m";
    const escapeYellow = "\033[0;33m";
    const escapeBlue = "\033[0;34m";
    const escapeMagenta = "\033[0;35m";
    const escapeCyan = "\033[0;36m";
    const escapeWhite = "\033[0;37m";

    const escapeError = "\033[41;37m";

    const escapeReset = "\033[0m";
}