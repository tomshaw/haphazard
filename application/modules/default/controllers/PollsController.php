<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class PollsController extends Zend_Controller_Action
{
    public function preDispatch()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            //return $this->_helper->redirector('index','index');	
        }
    }
    
    public function indexAction()
    {
        $request = $this->getRequest();
        
        $take = (bool) $this->_getParam('take', false);
        
        $view = (bool) $this->_getParam('view', false);
        
        $pollId = (int) $this->_getParam('id');
        
        $optionId = (int) $this->_getParam('option_id');
        
        $pollTracking = new Zend_Session_Namespace();
        
        $model = new Default_Model_Polls();
        
        if ($pollId && $request->isPost()) {
            $ip = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_ADDR');
            
            $row = $model->fetchRow('id = ' . (int) $pollId);
            
            $pollId = intval($row->id);
            
            $title = $row->title;
            
            list($dayx, $monthx, $yearx) = explode("/", $row->expires);
            $now    = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $expire = mktime(0, 0, 0, $monthx, $dayx, $yearx);
            if ($expire <= $now) {
                $expired = true;
            } else {
                $expired = false;
            }
            
            if ($expired) {
                $this->_helper->flash->addNotice('Sorry records indicate that this poll has already expired.');
                $this->_helper->redirector->gotoUrl('/polls');
            }
            
            if ($expired == false) {
                list($days, $months, $years) = explode("/", $row->starts);
                $starts = mktime(0, 0, 0, $months, $days, $years);
                if ($starts > $now) {
                    $started = false;
                } else {
                    $started = true;
                }
            } else {
                $started = false;
            }
            
            if (!$started) {
                $this->_helper->flash->addNotice('Sorry records indicate that this survey has not yet started.');
                $this->_helper->redirector->gotoUrl('/polls');
            }
            
            $pollsip = new Default_Model_PollsIp();
            
            $ipdata = $pollsip->checkIpAddress($pollId, $ip);
            
            if ($row->ip) { // Requires ip checking.
                if ($ipdata) { // A row in the database is matching this ip.
                    if ($row->interval) { // Survey allows interval voting.
                        if ($ipdata->created <= time()) { // If time has passed add time + interval again.
                            $pollsip->updateEntry(time() + $row->interval, array(
                                'poll_id' => $pollId,
                                'ip' => $ip
                            ));
                        } else {
                            $seconds = time() - $ipdata->created;
                            $minutes = round($seconds / 60);
                            $this->_helper->flash->addNotice('You must wait ' . preg_replace("/-/", "", $minutes) . ' minutes to participate in this survey again.');
                            $this->_helper->redirector->gotoUrl('/polls');
                        }
                    } else {
                        $this->_helper->flash->addNotice('Records indicate that you have already participated in this survey.');
                        $this->_helper->redirector->gotoUrl('/polls');
                    }
                } else {
                    $time = ($row->interval) ? (time() + $row->interval) : time();
                    $pollsip->insertEntry(array(
                        'poll_id' => $pollId,
                        'ip' => $ip,
                        'created' => $time
                    ));
                }
            } else {
                $pollsip->deleteEntry($pollId, $ip);
            }
            
            if ($row->cookie) { // Security checking via cookies.
                if (isset($pollTracking->store[$pollId])) { // Participant has taken part in this survey.
                    if ($row->interval) { // Is there a voting interval if so lets check.
                        if ($pollTracking->store[$pollId] <= time()) {
                            $pollTracking->store[$pollId] = time() + $row->interval;
                        } else {
                            $seconds = time() - $pollTracking->store[$pollId];
                            $minutes = round($seconds / 60);
                            $this->_helper->flash->addNotice('You must wait ' . preg_replace("/-/", "", $minutes) . ' minutes to participate in this survey again.');
                            $this->_helper->redirector->gotoUrl('/polls');
                        }
                    }
                }
                $pollTracking->store[$pollId] = ($row->interval) ? (time() + $row->interval) : time();
            }
            
            $pollopts = new Default_Model_PollsOptions();
            
            if (!$options = $pollopts->fetchRow('id = ' . $optionId)) {
                $this->_helper->flash->addError('The Poll you requested could not be found.');
                $this->_helper->redirector->gotoUrl('/polls');
            }
            
            $pollopts->updateVotes($optionId, $options->votes + 1);
            
            $this->_helper->flash->addSuccess('There are no polls available at this time.');
            $this->_helper->redirector->gotoUrl('/polls/view/' . $pollId);
            
        } else {
            $page = $this->_getParam('page', 1);
            
            $this->view->page = $page;
            
            $rowset = $model->fetchAll();
            
            $paginator = Zend_Paginator::factory($rowset);
            
            $paginator->setCurrentPageNumber($page);
            
            $paginator->setItemCountPerPage(10);
            
            $totalPolls = $paginator->getTotalItemCount();
            
            if ($totalPolls) {
                for ($i = 0; $i < $totalPolls; $i++) {
                    $id = $rowset[$i]->id;
                    
                    $class = 'polltake';
                    if (isset($pollTracking->store[$id])) {
                        if ($rowset[$i]->interval) {
                            if ($pollTracking->store[$id] >= time()) {
                                $class = 'pollclock';
                            }
                        } else {
                            $class = 'polltaken';
                        }
                    }
                    
                    $out[] = array(
                        'id' => $id,
                        'title' => $rowset[$i]->title,
                        'description' => $rowset[$i]->description,
                        'created' => $rowset[$i]->created,
                        'class' => $class
                    );
                    
                }
                
                $this->view->rows = $out;
                
            } else {
                $this->_helper->flash->addError('There are no polls available at this time.');
                $this->_helper->redirector->gotoUrl('/polls');
                
            }
            
        }
        
    }
    
    public function takeAction()
    {
        $id = (int) $this->_getParam('id');
        
        $ip = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_ADDR');
        
        $model = new Default_Model_Polls();
        
        if (null === ($row = $model->fetchRow('id = ' . intval($id)))) {
            $this->_helper->flash->addError('Could not locate poll.');
            $this->_helper->redirector->gotoUrl('/polls');
        }
        
        $tracking = new Zend_Session_Namespace();
        
        $tracking = ($tracking->polls) ? unserialize($tracking->polls) : array();
        
        $votedCookie = false;
        if ($row->cookie) {
            if (!empty($tracking[$id])) {
                $votedCookie = true;
            }
            if (!empty($tracking[$id]) && $tracking[$id] <= time()) {
                $votedCookie = false;
            }
        }
        
        if ($votedCookie) {
            $this->_helper->flash->addError('Records indicate that you have already participated in this poll.');
            $this->_helper->redirector->gotoUrl('/polls');
        }
        
        if (!$row->enabled) {
            $this->_helper->flash->addError('Sorry the Poll you have requested is inactive at this time.');
            $this->_helper->redirector->gotoUrl('/polls');
        }
        
        $this->view->id = $row->id;
        
        $this->view->title = $row->title;
        
        $this->view->description = $row->description;
        
        $model = new Default_Model_PollsOptions();
        
        if (false === ($options = $model->getOptions($id))) {
            $this->_helper->flash->addError('The system survey you requested is not present.');
            $this->_helper->redirector->gotoUrl('/polls');
        }
        
        $this->view->options = $options;
        
    }
    
    public function viewAction()
    {
        $id = (int) $this->_getParam('id');
        
        $model = new Default_Model_Polls();
        
        $poll = $model->fetchRow('id = ' . $id);
        
        $ip = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_ADDR');
        
        $pollTracking = new Zend_Session_Namespace();
        
        $pollTracking = ($pollTracking->polls) ? unserialize($pollTracking->polls) : array();
        
        if ($poll->confidential) {
            $this->_helper->flash->addNotice('The results of this poll have been kept confidential.');
            $this->_helper->redirector->gotoUrl('/polls');
        }
        
        if ($poll->votefirst) {
            if ($poll->cookie) {
                if (empty($pollTracking[$id])) {
                    $this->_helper->flash->addNotice('This Poll requires you to vote before viewing the results.');
                    $this->_helper->redirector->gotoUrl('/polls');
                }
            } elseif ($poll->ip) {
                $pollsip = new Default_Model_PollsIp();
                if (false !== ($ipdata = $pollsip->checkIpAddress($id, $ip))) {
                    $this->_helper->flash->addNotice('This Poll requires you to vote before viewing the results.');
                    $this->_helper->redirector->gotoUrl('/polls');
                }
            }
        }
        
        $pollOptions = new Default_Model_PollsOptions();
        
        $rows = $pollOptions->getResultsData($poll->id);
        
        $totaldata = $pollOptions->getVoteCount($poll->id);
        
        $totalVotes = $totaldata[0]->total;
        
        $this->view->total_votes = $totalVotes;
        
        $this->view->title = $poll->title;
        
        $this->view->description = $poll->description;
        
        $count = 1;
        foreach ($rows as $row) {
            $percent = round(($row->votes / $totalVotes) * 100);
            
            // 			if (($handle = @opendir('./img/polls/graph')) != false) {
            // 				while (($file = @readdir($handle)) != false) {
            // 					if ($file != '.' && $file != '..') {
            // 						$files[] = $file;
            // 					}
            // 				}
            // 				shuffle($files);
            // 			}
            
            // 			$rimages = rand(0, (count($files) - 1));
            
            $classes = array(
                'progress-info',
                'progress-success',
                'progress-warning',
                'progress-danger'
            );
            shuffle($classes);
            $randomClass = rand(0, (count($classes) - 1));
            
            $output[] = array(
                'count' => $count,
                'votes' => $row->votes,
                'options' => $row->options,
                'percent' => $percent,
                //'src' 		=> '/img/polls/graph/' . $files[$rimages],
                'width' => ($percent * 5) + 10,
                'class' => $classes[$randomClass]
            );
            
            $this->view->pollset = $output;
            
            $count++;
        }
        
        $this->render('results');
    }
    
}