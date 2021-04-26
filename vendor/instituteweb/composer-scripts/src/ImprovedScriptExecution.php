<?php
namespace InstituteWeb\ComposerScripts;

/*  | This project is free software and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2016-2017 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * Class ImprovedScriptExecution
 *
 * @package InstituteWeb\ComposerScripts
 */
class ImprovedScriptExecution
{
    const ALIAS_EVENT_NAME_PREFIX = '_';

    /**
     * Allows to use "bin/app" calls in script section (with slash) also under windows.
     * Normally this would fail, cause Windows expects backslashs in paths.
     *
     * @param \Composer\Script\Event $event
     * @param string $method The __METHOD__ which called this. The caller.
     * @return void
     */
    public static function apply(\Composer\Script\Event $event, $method = __METHOD__)
    {
        $event->stopPropagation();
        $allScripts = $event->getComposer()->getPackage()->getScripts();

        $eventScripts = $allScripts[$event->getName()];
        $scriptsToExecute = array();
        $startToRecord = false;
        foreach ($eventScripts as $script) {
            if ($startToRecord) {
                // if windows, convert unix directory separators to windows format
                if (DIRECTORY_SEPARATOR === '\\') {
                    $scriptParts = explode(' ', trim($script));
                    $scriptParts[0] = str_replace('/', '\\', $scriptParts[0]);
                    $script = implode(' ', $scriptParts);
                }
                $scriptsToExecute[] = $script;
            } else if ($script === '\\' . $method) {
                $startToRecord = true;
            }
        }

        // Update scripts
        $allScripts[self::ALIAS_EVENT_NAME_PREFIX . $event->getName()] = $scriptsToExecute;
        $event->getComposer()->getPackage()->setScripts($allScripts);
        // Execute alias event with all remaining scripts
        $event->getComposer()->getEventDispatcher()->dispatchScript(
            self::ALIAS_EVENT_NAME_PREFIX . $event->getName(),
            $event->isDevMode(),
            $event->getArguments(),
            $event->getFlags()
        );
    }
}
