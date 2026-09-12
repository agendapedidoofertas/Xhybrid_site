<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/hours.php';

$a = "Monday: Open 24 hours\nTuesday: Open 24 hours\nWednesday: Open 24 hours\nThursday: Open 24 hours\nFriday: Open 24 hours\nSaturday: Open 24 hours\nSunday: Open 24 hours";
echo "24h: " . implode(' | ', hours_format_pt($a)) . PHP_EOL;

$b = "Monday: 6:00 AM – 10:00 PM\nTuesday: 6:00 AM – 10:00 PM\nWednesday: 6:00 AM – 10:00 PM\nThursday: 6:00 AM – 10:00 PM\nFriday: 6:00 AM – 10:00 PM\nSaturday: 6:00 AM – 10:00 PM\nSunday: 7:00 AM – 10:00 PM";
echo "mix: " . implode(' || ', hours_format_pt($b)) . PHP_EOL;

$c = "Monday: 9:00 AM – 6:00 PM\nTuesday: 9:00 AM – 6:00 PM\nWednesday: 9:00 AM – 6:00 PM\nThursday: 9:00 AM – 6:00 PM\nFriday: 9:00 AM – 6:00 PM\nSaturday: 9:00 AM – 1:00 PM\nSunday: Closed";
echo "week: " . implode(' || ', hours_format_pt($c)) . PHP_EOL;
