<?php

declare (strict_types=1);
/**
 * Fuel is a fast, lightweight, community driven PHP 8.2+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */
namespace Fuel\Tasks;

/**
 * Robot example task
 *
 * Ruthlessly stolen from the beareded Canadian sexy symbol:
 *
 *		Derek Allard: http://derekallard.com/
 *
 * @package		Fuel
 * @version		1.0
 * @author		Phil Sturgeon
 */
class Robots
{
    /**
     * This method gets ran when a valid method name is not used in the command.
     *
     * Usage (from command line):
     *
     * php oil r robots
     *
     * or
     *
     * php oil r robots "Kill all Mice"
     *
     * @return string
     */
    public static function run(?string $speech = null): string
    {
        if (!isset($speech)) {
            $speech = 'KILL ALL HUMANS!';
        }
        return self::render_robot($speech, 'red');
    }
    /**
     * An example method that is here just to show the various uses of tasks.
     *
     * Usage (from command line):
     *
     * php oil r robots:protect
     *
     * @return string
     */
    public static function protect(): string
    {
        return self::render_robot('PROTECT ALL HUMANS', 'green');
    }
    /**
     * Render an ASCII robot with the given speech text and eye color.
     */
    private static function render_robot(string $speech, string $eye_color): string
    {
        $eye = \Cli::color('*', $eye_color);
        return \Cli::color("\n\t\t\t\t\t\"{$speech}\"\n\t\t\t          _____     /\n\t\t\t         /_____\\", 'blue') . "\n" . \Cli::color('			    ____[\\', 'blue') . $eye . \Cli::color('---', 'blue') . $eye . \Cli::color('/]____', 'blue') . "\n" . \Cli::color('			   /\ #\ \_____/ /# /\
			  /  \# \_.---._/ #/  \
			 /   /|\  |   |  /|\   \
			/___/ | | |   | | | \___\
			|  |  | | |---| | |  |  |
			|__|  \_| |_#_| |_/  |__|
			//\\\\  <\ _//^\\\\_ />  //\\\\
			\||/  |\//// \\\\\\\\/|  \||/
			      |   |   |   |
			      |---|   |---|
			      |---|   |---|
			      |   |   |   |
			      |___|   |___|
			      /   \   /   \
			     |_____| |_____|
			     |HHHHH| |HHHHH|', 'blue');
    }
}