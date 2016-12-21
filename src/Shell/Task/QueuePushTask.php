<?php
namespace App\Shell\Task;

use Queue\Shell\Task\QueueTask;
use Cake\Console\Shell;
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
use Exception;

/**
 * @author Dastan Rahimi
 */
class QueuePushTask extends QueueTask {


	/**
	 * @var int
	 */
	public $timeout = 120;

	/**
	 * @var int
	 */
	public $retries = 0;

	/**
	 * @var bool
	 */
	public $autoUnserialize = true;

	/**
	 * "Add" the task, not possible for QueuePushTask
	 *
	 * @return void
	 */
	public function add() {
		$this->err('Queue Push Task cannot be added via Console.');
		$this->out('Please use createJob() on the QueuedTask Model to create a Proper Push Task.');
	}

	/**
	 * QueuePushTask::run()
	 *
	 * @param mixed $data Job data
	 * @param int|null $id The id of the QueuedTask
	 * @return bool Success
	 */
	public function run($data, $id = NULL) {
			$this->loadModel('Subscribers');
			$title = $data['title'];
			$body = $data['body'];
			$link = $data['link'];
			$subscribers = $this->Subscribers->find('all')->where(['Subscribers.register' => 1]);
			$firefox = 'https://updates.push.services.mozilla.com/wpush/v1/';
			$chrome = 'https://android.googleapis.com/gcm/send/';
			foreach($subscribers as $subs){
					$notification = new Notification(
					$title, $body,
					$link,
					'/img/logo-192x192.png'
			);
			if($subs->browser == 'firefox'){
			$subscription = new Subscription(new Endpoint($firefox . $subs->subscriber), new PublicKey($subs->crpt_key), new AuthenticationTag($subs->auth));
			$pushServiceRegistry = new PushServiceRegistry();
			$pushServiceRegistry->addPushService(new MozillaPushService());
		}else{
			$subscription = new Subscription(new Endpoint($chrome . $subs->subscriber), new PublicKey($subs->crpt_key), new AuthenticationTag($subs->auth));
			$pushServiceRegistry = new PushServiceRegistry();
			$pushServiceRegistry->addPushService(new GooglePushService('AIzaSyCcicwpHi8Vthy8qteb_Io6GC0C6gjYDCE'));
		}
			$pushService = new AggregatePushService($pushServiceRegistry);
			$client = new Client($pushService);
			$res = $client->pushNotification($notification, $subscription);
			//if status 410 which is Gone remove token from the database
			if($res->getStatusCode() == '410'){
			Log::write('debug', 'Automatilcy Deleted Expired Token. Token:'. $subs->subscriber);
			$this->Subscribers->unsubscribe($subs->subscriber);
			}
		}
}

	/**
	 * Log message
	 *
	 * @param array $contents log-data
	 * @param mixed $log int for loglevel, array for merge with log-data
	 * @return void
	 */
	protected function _log($contents, $log) {
		$config = [
			'level' => LOG_DEBUG,
			'scope' => 'email',
		];
		if ($log !== true) {
			if (!is_array($log)) {
				$log = ['level' => $log];
			}
			$config = array_merge($config, $log);
		}
		/** for now
		Log::write(
			$config['level'],
			PHP_EOL . $contents['headers'] . PHP_EOL . $contents['message'],
			$config['scope']
		);
		*/
	}


}
