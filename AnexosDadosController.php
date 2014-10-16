<?php

App::uses('AppController', 'Controller');

/**
 * AnexosDados Controller
 *
 * @property AnexosDado $AnexosDado
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class AnexosDadosController extends AppController {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Paginator', 'Session');

    /**
     * index method
     *
     * @return void
     */
    public function index() {
        $this->AnexosDado->recursive = 0;
        $this->set('anexosDados', $this->Paginator->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->AnexosDado->exists($id)) {
            throw new NotFoundException(__('Invalid anexos dado'));
        }
        $options = array('conditions' => array('AnexosDado.' . $this->AnexosDado->primaryKey => $id));
        $this->set('anexosDado', $this->AnexosDado->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->AnexosDado->create();
            //debug($this->request->data); exit;
            if ($this->AnexosDado->save($this->request->data)) {
                $this->Session->setFlash(__('Anexo adicionado com sucesso!'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('Ocorreu um erro ao tentar salvar'));
            }
        }
        $convenios = $this->AnexosDado->Convenio->find('list');
        $this->set(compact('convenios'));
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->AnexosDado->exists($id)) {
            throw new NotFoundException(__('Invalid anexos dado'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->AnexosDado->save($this->request->data)) {
                $this->Session->setFlash(__('The anexos dado has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The anexos dado could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('AnexosDado.' . $this->AnexosDado->primaryKey => $id));
            $this->request->data = $this->AnexosDado->find('first', $options);
        }
        $convenios = $this->AnexosDado->Convenio->find('list');
        $this->set(compact('convenios'));
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->AnexosDado->id = $id;
        if (!$this->AnexosDado->exists()) {
            throw new NotFoundException(__('Invalid anexos dado'));
        }
        $this->request->allowMethod('post', 'delete');
        if ($this->AnexosDado->delete()) {
            $this->Session->setFlash(__('The anexos dado has been deleted.'));
        } else {
            $this->Session->setFlash(__('The anexos dado could not be deleted. Please, try again.'));
        }
        return $this->redirect(array('action' => 'index'));
    }

}
