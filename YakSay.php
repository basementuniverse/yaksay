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
     * An associative array of speaking animals and their offset positions
     */
    const ANIMALS = [
        'yak' => [
            'text' => '
(__)____
\../    |\
 -- VVVV
   || ||',
            'offset' => 2
        ],
        'yak-dead' => [
            'text' => '
(__)____
\xx/    |\
 -u VVVV
   || ||',
            'offset' => 2
        ],
        'yak-surprised' => [
            'text' => '
(__)____
\oo/    |\
 -- VVVV
   /\ /\\',
            'offset' => 2
        ],
        'monkey' => [
            'text' => '
   __
 o(..)o
w (-)   w _)
 \_/ \_/ (
   (__)___)
   m  m',
            'offset' => 1
        ],
        'monkey-dead' => [
            'text' => '
   __
 o(xx)o
  (u)     _
   / \_  ( \
  /(__)\__)
   m  m',
            'offset' => 1
        ],
        'monkey-surprised' => [
            'text' => '
   __
 o(oo)o
W (O)   W /
 \_/ \_/ (
   (__)___)
   m  m',
            'offset' => 1
        ],
        'seal' => [
            'text' => '
      _
     /..
 ___/ =o=
/ ___V_)>
\/',
            'offset' => -2
        ],
        'seal-dead' => [
            'text' => '
      _
     /xx
 ___/ =u=
/ ___V_)>
\/',
            'offset' => -2
        ],
        'seal-surprised' => [
            'text' => '
      _
     /oo
/\__/ =o=
\____<_)>',
            'offset' => -2
        ]
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yak:say {--roadkill} {--triggered} {--imagine} {--costume=yak} {message?}';

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
        // Give the yak a costume
        $yakIndex = array_key_exists($this->option('costume'), self::ANIMALS) ? $this->option('costume') : 'yak';

        // Optionall kill or surprise the yak
        if ($this->option('roadkill')) {
            $yakIndex .= '-dead';
        } elseif ($this->option('triggered')) {
            $yakIndex .= '-surprised';
        }
        $yak = substr(self::ANIMALS[$yakIndex]['text'], 2);

        // Create padding
        $verticalPadding = array_fill(0, self::VERTICAL_PADDING, '');
        $horizontalPadding = str_repeat(' ', self::HORIZONTAL_PADDING);

        // If the yak offset is negative, increase the horizontal margin
        $yakOffset = self::ANIMALS[$yakIndex]['offset'];
        $speechBubbleExtraMargin = 0;
        if ($yakOffset < 0) {
            $speechBubbleExtraMargin = abs($yakOffset);
            $yakOffset = 0;
        }

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
        $yakWidth = max(array_map('strlen', preg_split("/\r\n|\n|\r/", $yak)));
        $arrowOffset = floor($lineLength / 2);
        $yakCenterOffset = floor(($lineLength - $yakWidth) / 2);

        // Draw speech bubble
        $speechBubbleLeftBorder = $this->option('imagine') ? '(' : '|';
        $speechBubbleRightBorder = $this->option('imagine') ? ')' : '|';
        $speechBubbleArrow = $this->option('imagine') ? 'o' : 'v';
        $speechBubbleMargin = str_repeat(' ', self::HORIZONTAL_MARGIN + 1 + $speechBubbleExtraMargin);
        echo $speechBubbleMargin . str_repeat('-', $lineLength) . "\r\n";
        foreach ($lines as $line) {
            echo str_repeat(' ', self::HORIZONTAL_MARGIN + $speechBubbleExtraMargin) .
                $speechBubbleLeftBorder . $line . $speechBubbleRightBorder .
                "\r\n";
        }
        echo $speechBubbleMargin . str_repeat('-', $arrowOffset - 1) . $speechBubbleArrow . str_repeat('-', $lineLength - $arrowOffset);
        echo str_repeat("\r\n", self::VERTICAL_MARGIN + 1);

        // Draw the yak
        $yakMargin = str_repeat(' ', self::HORIZONTAL_MARGIN + 1 + $yakOffset + $yakCenterOffset);
        foreach (preg_split("/\r\n|\n|\r/", $yak) as $line) {
            echo $yakMargin . $line . "\r\n";
        }
        echo "\r\n\r\n";
    }
}
