<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Imagick;

class FlattenPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flatten-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $old_files = Storage::disk('old_pdf')->allFiles();
        $progressBar = $this->output->createProgressBar(count($old_files));

        $progressBar->start();
        foreach ($old_files as $old_file) {
            $file = Storage::disk('old_pdf')->path($old_file);

            $img = new Imagick();
            $img->setResolution(200, 200);
            $img->readImage($file);
            $img->setImageBackgroundColor('white');
            $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $img->scaleImage(2400, 0);
            $img->setImageProperty('Exif:Make', 'Imagick2');
            $img->setImageFormat('png');
            $image_bin = $img->getImageBlob();
            $img->clear();
            $img->destroy();

            $temp_img = tmpfile();
            fwrite($temp_img, $image_bin);

            $pdf = new Imagick(stream_get_meta_data($temp_img)['uri']);
            $pdf->setImageFormat('pdf');
            $pdf_bin = $pdf->getImageBlob();
            $pdf->clear();
            $pdf->destroy();
            Storage::disk('new_pdf')->put($old_file, $pdf_bin);

            fclose($temp_img);
            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
