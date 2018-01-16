<?php

namespace OkStuff\PhpNsq\Internals;

use Bramus\Monolog\Formatter\ColoredLineFormatter;

class CustomerLineFormatter extends ColoredLineFormatter
{
    private $coloredOrNot;

    public function __construct($coloredOrNot = true, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
    {
        $this->coloredOrNot               = $coloredOrNot;
        $this->allowInlineLineBreaks      = $allowInlineLineBreaks;
        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra;
        parent::__construct(null, null, null, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $vars = parent::normalize($record);

        $output = $this->format;

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.' . $var . '%')) {
                $output = str_replace('%extra.' . $var . '%', $this->stringify($val), $output);
                unset($vars['extra'][$var]);
            }
        }


        foreach ($vars['context'] as $var => $val) {
            if (false !== strpos($output, '%context.' . $var . '%')) {
                $output = str_replace('%context.' . $var . '%', $this->stringify($val), $output);
                unset($vars['context'][$var]);
            }
        }

        $output = $this->coloredOrNot($vars, $output);

        // remove leftover %extra.xxx% and %context.xxx% if any
        if (false !== strpos($output, '%')) {
            $output = preg_replace('/%(?:extra|context)\..+?%/', '', $output);
        }

        // Let the parent class to the formatting, yet wrap it in the color linked to the level
        return $output;
    }

    protected function coloredOrNot(&$vars, $output)
    {
        if (!$this->coloredOrNot) {
            foreach ($vars as $var => $val) {
                if (false !== strpos($output, '%' . $var . '%')) {
                    $output = str_replace('%' . $var . '%', $this->stringify($val), $output);
                }
            }

            return $output;
        }

        $coloredOutput = "[%datetime%] %channel%.%level_name%: ";
        foreach ($vars as $var => $val) {
            if (false !== strpos($coloredOutput, '%' . $var . '%')) {
                $coloredOutput = str_replace('%' . $var . '%', $this->stringify($val), $coloredOutput);
            }
        }
        //Get the Color Scheme
        $colorScheme   = $this->getColorScheme();
        $coloredOutput = $colorScheme->getColorizeString($vars['level']) . $coloredOutput . $colorScheme->getResetString();

        $normalOutput = "%message% %context% %extra%\n";
        if ($this->ignoreEmptyContextAndExtra) {
            if (empty($vars['context'])) {
                unset($vars['context']);
                $normalOutput = str_replace('%context%', '', $normalOutput);
            }

            if (empty($vars['extra'])) {
                unset($vars['extra']);
                $normalOutput = str_replace('%extra%', '', $normalOutput);
            }
        }

        foreach ($vars as $var => $val) {
            if (false !== strpos($normalOutput, '%' . $var . '%')) {
                $normalOutput = str_replace('%' . $var . '%', $this->stringify($val), $normalOutput);
            }
        }

        $output = $coloredOutput . $normalOutput;

        return $output;
    }
}
