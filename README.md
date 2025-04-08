# first-second-every-minute
Shorten long videos to one second every minute using the First Second of Every Minute (fsem).

Effectively you can take a 30 minute video and shorten it to 30 seconds.

Why would anyone want this? nobody would want this.

# Usage
```
php -f fsem.php video.file
```
output filename is video.fsem.mp4

mp4 was chosen later as converting from one format to 1-second formats yielded bad results.

# Requirements
- PHP (created and tested on 8.3.0RC4)
- [FFMpeg and FFProbe](https://ffmpeg.org/)

# Sources
- https://unix.stackexchange.com/questions/283878
- https://shotstack.io/learn/use-ffmpeg-to-concatenate-video/
- https://trac.ffmpeg.org/wiki/Seeking
- https://stackoverflow.com/questions/38996925/ffmpeg-concat-unsafe-file-name
- https://unix.stackexchange.com/questions/412519/ffmpeg-protocol-not-found-for-normal-file-name
