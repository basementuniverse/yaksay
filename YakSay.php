<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class YakSay extends Command
{
    /**
     * Maximum line length inside the speech bubble, including horizontal padding but not including horizontal margin
     */
    const MAX_LINE_LENGTH = 32;

    /**
     * Number of blank lines to add above and below the message inside the speech bubble
     */
    const VERTICAL_PADDING = 1;

    /**
     * Number of spaces between the message and the speech bubble side borders
     */
    const HORIZONTAL_PADDING = 1;

    /**
     * Horizontal offset of the speech bubble from the left edge of the terminal
     */
    const HORIZONTAL_MARGIN = 2;

    /**
     * Number of blank lines between the speech bubble and the yak
     */
    const VERTICAL_MARGIN = 1;

    /**
     * The yak will be centered relative to the speech bubble, then shifted across by this amount
     */
    const YAK_OFFSET = 2;

    /**
     * The yak itself
     */
    const YAK = <<<EOT
(__)____
\../    |\
 -- VVVV
   || ||
EOT;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yak:say {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get a yak to say things on your behalf';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Create padding
        $verticalPadding = array_fill(0, self::VERTICAL_PADDING, '');
        $horizontalPadding = str_repeat(' ', self::HORIZONTAL_PADDING);

        // Wrap input onto multiple lines
        $wrapped = wordwrap($this->argument('message'), self::MAX_LINE_LENGTH - (2 * self::HORIZONTAL_PADDING), "\r\n", true);

        // Find actual maximum line length
        $lines = preg_split("/\r\n|\n|\r/", $wrapped);
        $lineLength = max(array_map('strlen', $lines)) + 2 * self::HORIZONTAL_PADDING;

        // Pad lines
        $lines = array_map(function($line) use($horizontalPadding, $lineLength) {
                return str_pad($horizontalPadding . $line . $horizontalPadding, $lineLength);
            },
            array_merge($verticalPadding, $lines, $verticalPadding)
        );

        // Calculate offsets for the yak and the speech bubble
        $yakWidth = max(array_map('strlen', preg_split("/\r\n|\n|\r/", self::YAK)));
        $arrowOffset = floor($lineLength / 2);
        $yakOffset = floor(($lineLength - $yakWidth) / 2);

        // Draw speech bubble
        $speechBubbleMargin = str_repeat(' ', self::HORIZONTAL_MARGIN + 1);
        echo $speechBubbleMargin . str_repeat('-', $lineLength) . "\r\n";
        foreach ($lines as $line) {
            echo str_repeat(' ', self::HORIZONTAL_MARGIN) . '|' . $line . "|\r\n";
        }
        echo $speechBubbleMargin . str_repeat('-', $arrowOffset - 1) . 'v' . str_repeat('-', $lineLength - $arrowOffset);
        echo str_repeat("\r\n", self::VERTICAL_MARGIN + 1);

        // Draw the yak
        $yakMargin = str_repeat(' ', self::HORIZONTAL_MARGIN + 1 + self::YAK_OFFSET + $yakOffset);
        foreach (preg_split("/\r\n|\n|\r/", self::YAK) as $line) {
            echo $yakMargin . $line . "\r\n";
        }
        echo "\r\n\r\n";
    }
}
