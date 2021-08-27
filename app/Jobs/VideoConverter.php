<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Video;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;

class VideoConverter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $video;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = 'public/videos/'.$this->video->id . '.m3u8';
        $lowBitrate  = (new X264 ('aac'))->setKiloBitrate(500)->setVideoCodec('libx264');
        $midBitrate  = (new X264 ('aac'))->setKiloBitrate(1000)->setVideoCodec('libx264');
        $highBitrate = (new X264 ('aac'))->setKiloBitrate(3000)->setVideoCodec('libx264');

        FFMpeg::fromDisk('local')->open($this->video->path)
            ->exportForHLS()
            ->dontSortFormats()
            ->setSegmentLength(10)
            ->toDisk('local')
            ->addFormat($lowBitrate, function($media) {
                $media->addFilter(function ($filters) {
                    $filters->resize(new \FFMpeg\Coordinate\Dimension(640, 480));
                });
            })
            ->addFormat($midBitrate, function($media) {
                $media->addFilter(function ($filters) {
                    $filters->resize(new \FFMpeg\Coordinate\Dimension(1280, 960));
                });
            })
            ->addFormat($highBitrate, function($media) {
                $media->addFilter(function ($filters) {
                    $filters->resize(new \FFMpeg\Coordinate\Dimension(1280, 960));
                });
            })
            ->save($path);

        $this->video->update([
            'converted_path' => $path,
            'converted' => 1,
        ]);

        FFMpeg::cleanupTemporaryFiles();
    }
}
