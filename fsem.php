<?php
/* Location of dependencies */
$ffprobe = '/bin/ffprobe';
$ffmpeg = '/bin/ffmpeg';

/* Defining things */
if (!file_exists($ffprobe)) { die("ffprobe not found\n"); }
if (!file_exists($ffmpeg)) { die("ffmpeg not found\n"); }
if (!isset($argv[1])) { die("need video file as parameter\n"); }
if (!file_exists($argv[1])) { die("file ${argv[1]} doesn't exist\n"); }
if (file_exists('filename.txt')) { unlink('filename.txt'); }

$input = $argv[1];
$filename = pathinfo($input, PATHINFO_FILENAME);
$extension = 'mp4'; /* Assume defaults */
$output = $filename . '.fsem.' . $extension;

/* Lets get video length */
$command_output = '';
$status = exec($ffprobe . ' -v quiet -print_format json -show_format -show_streams ' . escapeshellarg($input), $command_output);
if ($status === false) { die("error running ffprobe\n"); }
$json_data = implode('', $command_output);
$json = json_decode($json_data, true);
if (is_null($json)) { die("error in decoding output from ffprobe\n"); }
if (!isset($json['format']['duration'])) { die("cannot retrieve video duration\n"); }
$duration = $json['format']['duration'];

/* A fixed timestamp for work files */
$timestamp = strval(time());

/* Now we loop every 60 seconds until we're past the video */
echo "Extracting: ";
$increment = 0;
$seconds_progress = 0;
$minute = 0;
$hour = 0;
while ($seconds_progress < $duration) {
	echo '.';
	$shell_minute = $minute;
	if ($minute < 10) { $shell_minute = '0' . $minute; }
	$incremental_filename = 'source' . '-' . $timestamp . '-' . $increment . '.' . $extension;
	if (file_exists($incremental_filename)) {
		unlink($incremental_filename);
	}
	if (!file_exists($incremental_filename)) {
		exec( $ffmpeg . ' -v quiet -ss ' . $hour . ':' . $shell_minute . ':0.0 -i ' . escapeshellarg($input) . ' -t 1 ' . escapeshellarg('file:' . $incremental_filename), $throwaway );
	}
	$increment = $increment + 1;
	$minute = $minute + 1;
	$seconds_progress = $seconds_progress + 60;
	if ($minute == 60) {
		$minute = 0;
		$hour = $hour + 1;
	}
}
echo "\n";

/* Combine them all and run the demuxer */
echo "Combining: ";
$concat = 'concat-' . $timestamp . '.txt';
$fp = fopen($concat, 'wt');
for ($inc = 0; $inc < $increment; $inc++) {
	fwrite($fp, 'file ' . escapeshellarg('source' . '-' . $timestamp . '-' . $inc . '.' . $extension) . "\n");
}
fclose($fp);
exec( $ffmpeg . ' -v quiet -f concat -safe 0 -i ' . escapeshellarg($concat) . ' -c copy ' . escapeshellarg('file:' . $output) );
file_put_contents('filename.txt', $output);
echo "\n";

/* Clean up files */
unlink($concat);
for ($inc = 0; $inc < $increment; $inc++) {
	unlink('source' . '-' . $timestamp . '-' . $inc . '.' . $extension);
}
