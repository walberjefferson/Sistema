<?php

App::uses('AppModel', 'Model');

/**
 * AnexosDado Model
 *
 * @property Convenios $Convenios
 */
class AnexosDado extends AppModel {

    /**
     * Display field
     *
     * @var string
     */
    public $displayField = 'descricao';

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
        'arquivo' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
            //'message' => 'Your custom message here',
            //'allowEmpty' => false,
            //'required' => false,
            //'last' => false, // Stop validation after this rule
            //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'descricao' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
            //'message' => 'Your custom message here',
            //'allowEmpty' => false,
            //'required' => false,
            //'last' => false, // Stop validation after this rule
            //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'convenios_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            //'message' => 'Your custom message here',
            //'allowEmpty' => false,
            //'required' => false,
            //'last' => false, // Stop validation after this rule
            //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
    );

    //The Associations below have been created with all possible keys, those that are not needed can be removed

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = array(
        'Convenio' => array(
            'className' => 'Convenio',
            'foreignKey' => 'convenios_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

    /*public function beforeSave($options = array()) {
        debug($this->data); exit;
        $this->data['AnexosDado']['arquivo']['name'] = $this->upload($this->data['AnexosDado']['arquivo']);
        debug($this->data);
        if (!empty($this->data['AnexosDado']['arquivo']['name'])) {
            $this->data['AnexosDado']['arquivo'] = $this->upload($this->data['AnexosDado']['arquivo']);
        } else {
            unset($this->data['AnexosDado']['arquivo']);
        }
        exit;
        return true;
        //echo 'before';
    }*/

    public function beforeSave($options = array()) {
        debug($this->data); exit;
        if (!empty($this->data['AnexosDado']['arquivo']['name'])) {
            $this->data['AnexosDado']['arquivo'] = $this->upload($this->data['AnexosDado']['arquivo'], $this->data['AnexosDado']['convenios_id'], 'files/convenios/' . $this->data['AnexosDado']['convenios_id']);
        } else {
            unset($this->data['AnexosDado']['arquivo']);
        }
        return true;
    }

    public function upload($arquivo, $album_id, $dir = 'files', $img = true) {
        if (!empty($arquivo['name'])) {
            if ($arquivo['error'] > 0 and $arquivo['size'] == 0) {
                throw new NotImplementedException('Alguma coisa deu errado, o erro retornado foi: ' . $arquivo['erro'] . ' e o tamanho da imagem foi: ' . $arquivo['size'] . '!');
            }

            $dir = $this->diretorioUpload($dir, $album_id);

            $arquivo['name'] = $this->nomeArquivo($arquivo, $dir);
            if ($img) {
                $this->tratarImagem($arquivo, $dir, $album_id);
            } else {
                $this->move_arquivos($arquivo, $dir);
            }

            return $arquivo['name'];
        } else {
            throw new NotImplementedException('O campo não é do tipo file!');
        }
    }

    protected function diretorioUpload($dir) {
        $dir = WWW_ROOT . str_replace('/', DS, $dir) . DS;
        if (!is_dir($dir)) {
            App::uses('Folder', 'Utility');
            $folder = new Folder();
            if (!$folder->create($dir)) {
                throw new NotImplementedException('Você não tem permissão para criar ' . $dir);
            }
        }
        return $dir;
    }

    protected function nomeArquivo($arquivo, $dir) {
        $pathinfo = pathinfo($dir . $arquivo['name']);
        $pathinfo['filename'] = $this->stringParaWeb($pathinfo['filename']);
        App::uses('Folder', 'Utility');
        $folder = new Folder($dir);
        $files = $folder->read();
        $filename = $pathinfo['filename'];
        $conta = 2;
        while (in_array($filename . '.' . $pathinfo['extension'], $files[1])) {
            $filename = $pathinfo['filename'] . '-' . $conta;
            $conta++;
        }
        return $filename . '.' . $pathinfo['extension'];
    }

     /*public function nomeArquivo($imagem, $dir)
      {
      $imagem_info = pathinfo($dir.$imagem['name']);
      $imagem_nome = $this->trata_nome($imagem_info['filename']).'.'.$imagem_info['extension'];
      $conta = 2;
      while (file_exists($dir.$imagem_nome)) {
      $imagem_nome  = $this->trata_nome($imagem_info['filename']).'-'.$conta;
      $imagem_nome .= '.'.$imagem_info['extension'];
      $conta++;
      }
      $imagem['name'] = $imagem_nome;
      return $imagem;
      } */

    protected function stringParaWeb($string) {
        $string = strtolower(Inflector::slug($string, '-'));
        return $string;
    }

    /*protected function lerAlbum($album_id) {
        $retorno = Cache::read('album_id_' . $album_id, 'short');
        if (!$retorno) {
            $conditions = array(
                'Album.id' => $album_id
            );

            $fields = array(
                'Album.id',
                'imagem_w',
                'imagem_h',
                'thumb_w',
                'thumb_h',
                'thum_crop',
                'thumb_pb',
            );

            $recursive = -1;

            $retorno = $this->Album->find('first', compact('conditions', 'fields', 'recursive'));
            Cache::write('album_id_' . $album_id, $retorno, 'short');
        }

        return $retorno;
    }

    protected function tratarImagem($arquivo, $dir, $album_id) {
        $album = $this->lerAlbum($album_id);
        $album = $album['Album'];

        App::import('Vendor/wideimage', 'WideImage');
        $img = WideImage::load($arquivo['tmp_name']);

        if (($album['imagem_w'] > $img->getWidth()) and ( $album['imagem_h'] > $img->getHeight())) {
            $img = $img->resize($album['imagem_w'], $album['imagem_h'], 'outside');
        }

        $img_thumb = $img->resize($album['thumb_w'], $album['thumb_h'], 'outside');

        if ($album['thum_crop']) {
            $half_w = $album['thumb_w'] / 2;
            $half_h = $album['thumb_h'] / 2;
            $img_thumb = $img_thumb->crop('50%-' . $half_w, '50%-' . $half_h, $album['thumb_w'], $album['thumb_h']);
        }

        if ($album['thumb_pb']) {
            $img_thumb_pb = $img_thumb->asGrayscale();
        }

        $img->saveToFile($dir . $arquivo['name']);
        $img_thumb->saveToFile($dir . 'thumb_' . $arquivo['name']);
        if ($album['thumb_pb'])
            $img_thumb_pb->saveToFile($dir . 'thumb_pb_' . $arquivo['name']);
    }*/

    protected function move_arquivos($arquivo, $dir) {
        App::uses('File', 'Utility');
        $arquivo_final = new File($arquivo['tmp_name']);
        $arquivo_final->copy($dir . $arquivo_final['name']);
        $arquivo->close();
    }

}
