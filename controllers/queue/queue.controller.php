<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
ignore_user_abort();

use Aws\S3\S3Client;
use Aws\S3\Model\ClearBucket;


set_time_limit(0);

class queue extends service
{
    public $tube = false;
    public $queue = false;

    public function __construct()
    {
        $this->allowed_actions = array('view');

        $this->_preprocess();
    }


    protected function view()
    {
        $this->_format='json';
        $this->tube = $_GET['tube'] ?? false;

        if ($this->tube) {

            $this->getQueue();

            // pick a job and process it
            if ($this->queue->stats()['current-jobs-ready'] > 0 && $job = $this->queue->reserve()) {
                $received = json_decode($job->getData(), true);
                $this->_result = $received['body']?? ['message' => 'Empty result', 'target' => 'status', 'type' => 'console', 'tube' => $this->tube];
                $this->queue->delete($job);
            } else {
                $this->_result = ['message' => 'No jobs', 'target' => 'status', 'type' => 'console', 'tube' => $this->tube];
            }
        } else {
            $this->_result = ['message' => 'No Tube submitted', 'target' => 'error', 'type' => 'append'];
        }
    }

    protected function getQueue()
    {
        $this->queue = new Pheanstalk('127.0.0.1:11300');

        $this->queue->watch($this->tube);
        if ($this->queue->stats()['current-jobs-ready'] == 0) {
            $this->_result = ['message' => 'No jobs', 'target' => 'status', 'type' => 'console', 'tube' => $this->tube];
        }
    }
}
