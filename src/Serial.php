<?php
namespace PHPDioSerial;
class Serial
{
    //默认一次读取1024字节 防止内存boom！！！！
    const DEFAULT_LENGTH = 1024;


// 设备静态变量 用于单例设备
    private static $_device=array();
    private $deviceName;


    private $device;
    private $context;

    // 串口连接
    private $serial;

    //单例  只能从这个connect 实例化
    public static function connect($deviceName,$options='115200,8,1,0',$isCanonical=0)
    {
        if(! (self::$_device[$deviceName] instanceof self) )
        {
            self::$_device[$deviceName]= new self($deviceName,$options,$isCanonical);
        }
        return self::$_device[$deviceName];
    }
    //构析函数 连接设备
    private function __construct($device, $options,$isCanonical=0)
    {
        // Attempt to set device...
        if (!$this->_setDevice($device))
        {
            throw new \Exception("Unable to set device for serial connection");
        }
        $this->_setContext($options,$isCanonical);

        // Create direct IO file handle with specified flags
        $this->serial = fopen($this->device, 'r+',false,$this->context );
    }


    private function _setDevice($deviceName)
    {
        if (file_exists('dio.serial://'.$deviceName))
        {
            $this->device = 'dio.serial://'.$deviceName;
            $this->deviceName=$deviceName;
            return true;
        }
        return false;
    }

    private function _getDevice()
    {
        return $this->device;
    }

    private function _setContext($option,$is_canonical)
    {
        $optionArr=explode(',',$option);
        $optionArr=array_push($optionArr,$is_canonical);
        $optionArr=array_map('intval',$optionArr);
        $this->context = stream_context_create(array('dio' =>
            array('data_rate' => $optionArr[0],
                'data_bits' => $optionArr[1],
                'stop_bits' => $optionArr[2],
                'parity' => $optionArr[3],
                'is_canonical' => $optionArr[4])));
    }

    private function _getContext()
    {
        return $this->context;
    }


    public function __destruct()
    {
        if (isset($this->serial))
        {
            $this->close();
        }
    }

    public function close()
    {
        if (isset($this->serial))
        {
            fclose($this->serial);
            unset(self::$_device[$this->deviceName]);
        }
        return true;
    }

    // Read data from serial port
    public function read($eof='',$length = self::DEFAULT_LENGTH)
    {
         while(1)
         {
             $byte=fgetc($this->serial);
             $bytes .= $byte;
             if($eof &&  $byte===$eof || strlen($bytes)==$length)
             {
                 break;
             }
         }
        return $bytes;
    }

    // Write data to serial port
    public function write($data, $length = self::DEFAULT_LENGTH)
    {
        $bytes = fwrite($this->serial, $data);
        return $bytes;
    }
}
