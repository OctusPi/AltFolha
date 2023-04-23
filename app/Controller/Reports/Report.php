<?php
namespace Octus\App\Controller\Reports;

use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\HTMLParserMode;
use Mpdf\QrCode\Output\Png;
use Octus\App\Model\EntityCompany;
use Octus\App\Utils\Html;
use Octus\App\Utils\View;
use Octus\App\Utils\Utils;
use Octus\App\Utils\Alerts;


abstract class Report
{
    protected ?EntityCompany $infosys;

    public function __construct(?EntityCompany $infosys = null)
    {
        $this->infosys = $infosys;
    }

    public function getReport(string $title, string $content, array $params = []):string
    {
        $base = [
            'title'     => $title,

            'h_logo'    => Html::imgReport(Utils::attr('logo', $this->infosys), 'Logo Sistema', 'img_logo'),
            'h_company' => Utils::attr('company', $this->infosys),
            'h_address' => Utils::attr('endereco', $this->infosys),
            'h_infos'   => Utils::attr('email', $this->infosys).' '.Utils::attr('telefone', $this->infosys),
            

            'content'   => View::renderView($content, $params),

            'f_track'   => Utils::at('qrcode', $params)

            
        ];

        return View::renderView('reports/default', $base);

    }

    public function exportReport(string $title, string $html, string $mode = 'open'):string
    {
        return match($mode){
            'open'  => $this->exportOpen($title, $html),
            'save'  => $this->exportSave($title, $html),
            default => Alerts::notify(Alerts::STATUS_WARNING, 'Falhar ao selecionar modo de exportaçao')
        };
    }

    private function exportOpen(string $title, string $html):string
    {
        $style  = file_get_contents(__DIR__ . '/../../../resources/css/report.css');
        $params = [
            'mode'              => 'utf-8',
            'format'            => 'A4-P',
            'default_font_size' => 8,
            'default_font'      => 'monospace'
        ];

        try {
            $mpdf =  new Mpdf($params);
            $mpdf -> WriteHTML($style, HTMLParserMode::HEADER_CSS);
            $mpdf -> WriteHTML($html, HTMLParserMode::HTML_BODY);
            $mpdf -> Output($title.'.pdf', "D");

            return Alerts::notify(Alerts::STATUS_OK, 'Relatório Exportado');

        } catch (\Throwable $th) {
            return Alerts::notify(Alerts::STATUS_ERROR, 'Falha ao Exportar Relatório '.$title);
        }
    }

    private function exportSave($title, $html):string
    {
        $style  = file_get_contents(__DIR__ . '/../../../resources/css/report.css');
        $path   = __DIR__.'/../../../exports/reports/';
        $params = [
            'mode'              => 'utf-8',
            'format'            => 'A4-P',
            'default_font_size' => 8,
            'default_font'      => 'monospace'
        ];

        try {
            $mpdf =  new Mpdf($params);
            $mpdf -> WriteHTML($style, HTMLParserMode::HEADER_CSS);
            $mpdf -> WriteHTML($html, HTMLParserMode::HTML_BODY);
            $mpdf -> Output($path.$title.'.pdf', "F");

            return Alerts::notify(Alerts::STATUS_OK, Html::hrefmsg('Baixar Relatório', 'exports/reports/'.$title.'.pdf'));

        } catch (\Throwable $th) {
            return Alerts::notify(Alerts::STATUS_ERROR, 'Falha ao Exportar Relatório '.$title);
        }
    }

    public function getQrcode(?string $value):?string
    {
        $qrcode = new QrCode($value);
        $qrpng  = (new Png)->output($qrcode,70);
        return base64_encode($qrpng);
    }

}