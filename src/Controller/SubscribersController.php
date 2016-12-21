<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Core\Configure\Engine\PhpConfig;
use Cmnty\Push\AggregatePushService;
use Cmnty\Push\Client;
use Cmnty\Push\Crypto\AuthenticationTag;
use Cmnty\Push\Crypto\PublicKey;
use Cmnty\Push\EndPoint;
use Cmnty\Push\GooglePushService;
use Cmnty\Push\MozillaPushService;
use Cmnty\Push\Notification;
use Cmnty\Push\PushServiceRegistry;
use Cmnty\Push\Subscription;

/**
 * Subscribers Controller
 *
 * @property \App\Model\Table\SubscribersTable $Subscribers
 */
class SubscribersController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $subscribers = $this->paginate($this->Subscribers);

        $this->set(compact('subscribers'));
        $this->set('_serialize', ['subscribers']);
    }

    /**
     * Push Job method
     *
     * @return \Cake\Network\Response|null
     */
    public function sendPush()
    {
      //Configure::write('debug', 0);
      if ($this->request->is('post')) {
          $this->loadModel('Queue.QueuedTasks');
          $this->QueuedTasks->createJob('Push', $this->request->data);
          $this->Flash->success(__('Push Sent to all users'));
          return $this->redirect(['action' => 'index']);
        }

    }

    /**
     * View method
     *
     * @param string|null $id Subscriber id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $subscriber = $this->Subscribers->get($id, [
            'contain' => []
        ]);

        $this->set('subscriber', $subscriber);
        $this->set('_serialize', ['subscriber']);
    }

    /**
     * Add method - API FOR PUSH
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $subscriber = $this->Subscribers->newEntity();
        if ($this->request->is('post')) {
            $subsID = $this->request->data['subscriber'];
            $subscribers = $this->Subscribers->findBySubscriber($subsID);
            if(!$subscribers->isEmpty()){
              $this->set("status", "OK");
              $this->set("message", "Duplicate");
            }else{
            $subscriber = $this->Subscribers->patchEntity($subscriber, $this->request->data);
            if ($this->Subscribers->save($subscriber)) {
              $this->set("status", "OK");
              $this->set("message", "You are good");
            } else{
              $this->set("status", "NOT OK");
              $this->set("message", "Error");
            }
        }
    }
    $this->set(compact('subscriber'));
    $this->set("_serialize", array("status", "message", "['subscriber']"));
    }




    /**
     * unsubscribe method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function unsubscribe($subsID = null)
    {
        $subscriber = $this->Subscribers->newEntity();
        if ($this->request->is('post')) {
          if(!empty($subsID)){
            $subsID = $subsID;
            }else{
            $subsID = $this->request->data['subscriber'];
          }
            $subscribers = $this->Subscribers->findBySubscriber($subsID);
            $row = $subscribers->first();
            $id  = $row->id;
            if(!$subscribers->isEmpty()){
              $subscriber = $this->Subscribers->get($id);
              if ($this->Subscribers->delete($subscriber)) {
                $this->set("status", "OK");
                $this->set("message", "You are good");
              } else{
                $this->set("status", "NOT OK");
                $this->set("message", "Error");
              }
            }else{
              $this->set("status", "OK");
              $this->set("message", "No subscription ID found!");
        }
    }
    $this->set(compact('subscriber'));
    $this->set("_serialize", array("status", "message", "['subscriber']"));
    }

    /**
     * Edit method
     *
     * @param string|null $id Subscriber id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $subscriber = $this->Subscribers->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $subscriber = $this->Subscribers->patchEntity($subscriber, $this->request->data);
            if ($this->Subscribers->save($subscriber)) {
                $this->Flash->success(__('The subscriber has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The subscriber could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('subscriber'));
        $this->set('_serialize', ['subscriber']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Subscriber id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $subscriber = $this->Subscribers->get($id);
        if ($this->Subscribers->delete($subscriber)) {
            $this->Flash->success(__('The subscriber has been deleted.'));
        } else {
            $this->Flash->error(__('The subscriber could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
