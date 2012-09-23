<?
/*
 * $img = new Imagem($url_jpg);
 * $img->resize(1024, 1024, true);
 * $img->saveImagem();
 * $img->showImagem();
 */
class Imagem {

    public $imagem;
    public $width;
    public $height;

    private $type;
    private $attr;

    private $nLargura = 400;
    private $nAltura  = 300;

    public $orientation = "PORTRAIT";
    private $_save      = false;
    private $nName      = "";
    
    private $_marca     = false;
    private $_marca_img;
    private $_mAgua     = array();
    public $_marcaPosition = array();

    public function __construct($imagem, $marca = false, $_marca_img = '') {
        $this->imagem = $imagem;
        list($this->width, $this->height, $this->type, $this->attr) = @getimagesize($this->imagem);
        $this->defineOrientation();
        $this->_marca = $marca;
        $this->_marca_img = $_marca_img;
        
    	$this->nLargura = $this->width;
    	$this->nAltura  = $this->height;
    	
        if ($this->_marca) {
        	$this->gerarMarca();
        }

    }

    public function  __set($name,  $value) {
        $this->$name = $value;
    }

    public function  __get($name) {
        return $this->$name;
    }
    
    public function getExtensao() {
    	$ext = explode(".", $this->imagem);
        $ext = ".".$ext[count($ext) - 1];
        
        return $ext;
    }

    public function saveImagem($nome = "temp") {
        $this->nName = $nome;
        $this->_save = true;

        $ext = explode(".", $this->imagem);
        $ext = $ext[count($ext) - 1];
        
        //$nome = $nome;
        /*
        if(!is_file($this->imagem)) {
          return false;
        }
        */
        $cp = copy($this->imagem, $this->nName);
        
        if(!$cp) {
			return false;
        }
        
        $this->imagem = $nome;
        
        $resultado = "";

        switch($this->type) {

            case "1" :
                $resultado = $this->createGIF();
                break;

            case "3" :
                $resultado = $this->createPNG();
                break;

            default :
                $resultado = $this->createJPG();
                break;

        }
        
    }

    private function defineOrientation() {
        
        if ( $this->width >= $this->height ) {
            $this->orientation = "LANDSCAPE";
        } else {
            $this->orientation = "PORTRAIT";
        }

    }
    
    private function gerarMarca() {
    	
        $imagem_marca = new Imagem($this->_marca_img);
        
        $this->_mAgua['img'] = $imagem_marca;
        
        switch ($imagem_marca->type) {
        	
        	case 1 : 
        		$this->_mAgua['source'] = imagecreatefromgif($imagem_marca->imagem);
        		break;
        	
        	case 3 :
        		$this->_mAgua['source'] = imagecreatefrompng($imagem_marca->imagem);
        		break;
        		
        	default :
        		$this->_mAgua['source'] = imagecreatefromjpeg($imagem_marca->imagem);
        		break;
        	
        }
    }
    
    public function showImagem() {

        $resultado = "";

        switch($this->type) {

            case "1" :
                $resultado = $this->createGIF();
                break;

            case "3" :
                $resultado = $this->createPNG();
                break;

            default :
                $resultado = $this->createJPG();
                break;

        }

        return $resultado;
        
    }

    public function resize($largura, $altura, $force = false) {

        if ( $force ) {
		
            $this->nLargura = $largura;
            $this->nAltura  = $altura;
			
			if ( !$largura ) {
				$this->nLargura = round(($this->width * $altura) / $this->height);
			}
			
			if ( !$altura ) {
				$this->nAltura = round(($this->height * $largura) / $this->width);
			}
			
        } else {
        	
            if ( $this->orientation == "PORTRAIT" ) {

                if ( ($largura != 0) && ($altura == 0) ) {
                    $this->nLargura = $largura;
                    $this->nAltura = round(($this->height * $largura) / $this->width);
                } elseif ( ($largura == 0) && ($altura != 0) ) {
                    $this->nAltura = $altura;
                    $this->nLargura = round(($this->width * $altura) / $this->height);
                } else {
                    $this->nLargura = $largura;
                    $this->nAltura = round(($this->height * $largura) / $this->width);

                    if ( $this->nAltura > $altura ) {
                        $this->nLargura = round(($this->width * $altura) / $this->height);
                    }

                }

            } else {

                if ( ($largura != 0) && ($altura == 0) ) {
                    $this->nLargura = $largura;
                    $this->nAltura = round(($this->height * $largura) / $this->width);
                } elseif ( ($largura == 0) && ($altura != 0) ) {
                    $this->nAltura = $altura;
                    $this->nLargura = round(($this->width * $altura) / $this->height);
                } else {
                    $this->nLargura = $largura;
                    $this->nAltura = round(($this->height * $largura) / $this->width);

                    if ( $this->nAltura > $altura ) {
                        $this->nLargura = round(($this->width * $altura) / $this->height);
                    }

                }

            }
			
        }
        
    }
    
    private function createJPG() {

        if ( !$this->_save ) {
            header("Content-Type: image/jpeg");
        }

        $source  = imagecreatefromjpeg($this->imagem);
        $destino = imagecreatetruecolor($this->nLargura, $this->nAltura);

        imagecopyresampled($destino, $source, 0, 0, 0, 0, $this->nLargura, $this->nAltura, $this->width, $this->height);
        
        if ($this->_marca && !empty($this->_marca_img)) {
        	$larg = ($this->nLargura - $this->_mAgua['img']->width);
        	$altu = ($this->nAltura - $this->_mAgua['img']->height);
        	
        	imagecopymerge($destino, $this->_mAgua['source'], $larg, $altu, 0, 0, $this->_mAgua['img']->width, $this->_mAgua['img']->height, 40);
        }

        if ( !$this->_save ) {
            $nImagem = imagejpeg($destino, null, 100);
            return $nImagem;
        } else {
            $nImagem = imagejpeg($destino, $this->nName, 100);
            imagedestroy($source);
            imagedestroy($destino);
        }

    }

    private function createGIF() {

        if ( !$this->_save ) {
            header("Content-Type: image/gif");
        }

        $source  = imagecreatefromgif($this->imagem);
        $destino = imagecreatetruecolor($this->nLargura, $this->nAltura);

        imagecopyresampled($destino, $source, 0, 0, 0, 0, $this->nLargura, $this->nAltura, $this->width, $this->height);

        if ( !$this->_save ) {
            $nImagem = imagegif($destino);
            return $nImagem;
        } else {
            $nImagem = imagegif($destino, $this->nName);
            imagedestroy($source);
            imagedestroy($destino);
        }

    }

    private function createPNG() {

        if ( !$this->_save ) {
            header("Content-Type: image/png");
        }

        $source  = imagecreatefrompng($this->imagem);
        $destino = imagecreatetruecolor($this->nLargura, $this->nAltura);

        imagecopyresampled($destino, $source, 0, 0, 0, 0, $this->nLargura, $this->nAltura, $this->width, $this->height);

        if ( !$this->_save ) {
            $nImagem = imagepng($destino);
            return $nImagem;
        } else {
            $nImagem = imagepng($destino, $this->nName);
            imagedestroy($source);
            imagedestroy($destino);
        }

    }

}
?>