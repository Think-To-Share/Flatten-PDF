<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Mpdf\Mpdf;

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
            $img->mergeImageLayers(Imagick::LAYERMETHOD_MERGE);
            $img->scaleImage(2400, 0);
            $img->setImageProperty('Exif:Make', 'Imagick2');
            $img->setImageFormat('pdf');
            $pdf_bin = $img->getImageBlob();
            $img->clear();
            $img->destroy();
            Storage::disk('new_pdf')->put($old_file, $pdf_bin);

            $pdf = new mPDF(['format' => 'A4-L']);
            $pagecount = $pdf->SetSourceFile(Storage::disk('new_pdf')->path($old_file));
            $tplId = $pdf->ImportPage($pagecount);
            $pdf->UseTemplate($tplId);
            $bin = $pdf->Output($old_file, "S");

            Storage::disk('new_pdf')->put($old_file, $bin);

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
