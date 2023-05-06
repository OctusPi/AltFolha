<?php
namespace Octus\App\Utils;

use Octus\App\Utils\Logs;
use Octus\App\Utils\Utils;

class Files
{
    const PATH = __DIR__.'/../../uploads/';

    private array $status;
    
    /**
     * Method return array with key|value of accpets files to upload
     *
     * @return array
     */
    private function getAccept():array
    {
        return [
            'text/plain' => '.csv',
            'text/csv'   => '.csv',
            'image/png'  => '.png',
            'image/jpeg' => '.jpg',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
            'application/vnd.ms-excel' => '.xlsx'

        ];
    }

    /**
     * Method checks if file is accpet to upload in system
     *
     * @param string $type
     * @return bool
     */
    private function isAccept(string $type):bool
    {
        return array_key_exists($type, $this->getAccept());
    }

    /**
     * Method return extension file (.ext)
     *
     * @param string $type
     * @return string|null
     */
    private function getExt(string $type):?string
    {
        return Utils::at($type, $this->getAccept());
    }

    /**
     * Method genetate a randon name based in date moment to upload
     *
     * @return string
     */
    private function randonName(?string $name = null):string
    {
        return $name == null 
        ? md5(uniqid(rand(), true))
        : $name;
    }

    /**
     * Method feed status to upload files instance
     *
     * @param bool $status
     * @param string $name
     * @param string $info
     * @return void
     */
    private function feedStatus(bool $status, string $name, string $info):void
    {
        $this->status['status'][]  = $status;
        $this->status['file'][]    = $name;
        $this->status['info'][]    = $info;
    }

    /**
     * Method upload multiple files
     *
     * @param array|null $files
     * @param bool $randname
     * @return void
     */
    private function upMulti(?array $files, bool $randname = true, ?string $fixedrand = null):void
    {
        if($files != null){
            foreach($files['name'] as $key => $value){
                
                //stores type and name file in variable to reuse
                $type = $files['type'][$key];
                $name = ($randname ? $this->randonName($fixedrand) : $value).$this->getExt($type);
                
                if($this->isAccept($type)){
                    if(move_uploaded_file($files['tmp_name'][$key], self::PATH.$name)){
                        $this->feedStatus(true, $name, 'Upload realizado com Sucesso');
                    }else{
                        $this->feedStatus(false, $name, $files['error'][$key]);
                    }
                }else{
                    $this->feedStatus(false, $name, 'Tipo de arquivo não aceito!');
                }
            }
        }
    }

    /**
     * Method upload a single file
     *
     * @param array|null $files
     * @param bool $randname
     * @return void
     */
    private function upSingle(?array $files, bool $randname = true, ?string $fixedrand = null):void
    {
        if($files != null){
            //stores type and name file in variable to reuse
            $type = $files['type'];
            $name = ($randname ? $this->randonName($fixedrand) : $files['name']).$this->getExt($type);

            if($this->isAccept($type)){
                if(move_uploaded_file($files['tmp_name'], self::PATH.$name)){
                    $this->feedStatus(true, $name, 'Upload realizado com sucesso!');
                }else{
                    $this->feedStatus(false, $name, $files['error']);
                }
            }else{
                $this->feedStatus(false, $name, 'Tipo de arquivo não aceito!');
            }
        }
    }

    /**
     * Method exec upload multiple or sigle file and return array status with success or fail and infos
     *
     * @param array|null $files
     * @param bool $randname
     * @return array|null
     */
    public function up(?array $files, bool $randname = true, ?string $fixedrand = null):?array
    {
        if($files['tmp_name'] != null)
        {
            //exec upload files
            (is_array($files['name'])) ? 
            $this->upMulti($files, $randname, $fixedrand) : 
            $this->upSingle($files, $randname, $fixedrand);

            //write log to any file attempt upload
            if($this->status != null):
                foreach($this->status['status'] as $key => $value){
                    Logs::writeLog(($value ? 'SUCCESS: ' : 'ERROR: ').$this->status['info'][$key]);
                }
            endif;
        }else{
            $this->feedStatus(false, 'Undefined', 'Nemhum arquivo enviado...');
        }

        //return status upload array
        return $this->status;
    }

    /**
     * Method return status execution upload files
     *
     * @return array
     */
    public function getstatus():array
    {
        return $this->status;
    }
}