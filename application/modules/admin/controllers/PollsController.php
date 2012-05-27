<?php
/**
 * Main administrative weblog listing.
 *
 */
class Admin_PollsController extends Zend_Controller_Action
{

    public function indexAction()
    {
		$page = $this->_getParam('page',1);

		$this->view->page = $page;
		
		$model = new Default_Model_Polls();

		$rowset = $model->fetchAll();
		
		$paginator = Zend_Paginator::factory($rowset);

		$paginator->setCurrentPageNumber($page);
	
		$paginator->setItemCountPerPage(10);
		
		$this->view->rows = $paginator;    	
    }
	
	public function editAction() 
	{	
		$request = $this->getRequest();
		
		$pollId = (int) $this->_getParam('id');
		
		$option_id = (int) $this->_getParam('option_id');
		
		$create = (bool) $this->_getParam('create');
		
		$listing = (bool) $this->_getParam('listing');
		
		$polls = new Default_Model_Polls();
			
		$row = $polls->fetchRow('id = ' . $pollId);

		$pollopts = new Default_Model_PollsOptions();
		
		$select = $pollopts->select()->where('poll_id = ?', $pollId)->order('order_id ASC');
		
		$options = $pollopts->fetchAll($select);

		if ($pollId && $request->isPost() && !$listing) {

			$starts_days = $this->_getParam('starts_days');
			$starts_months = $this->_getParam('starts_months');
			$starts_years = $this->_getParam('starts_years');
	
			$expires_days = $this->_getParam('expires_days');
			$expires_months = $this->_getParam('expires_months');
			$expires_years = $this->_getParam('expires_years');
	
			$vote_years = $this->_getParam('vote_years');
			$vote_months = $this->_getParam('vote_months');
			$vote_days = $this->_getParam('vote_days');
			$vote_hours = $this->_getParam('vote_hours');
			$vote_minutes = $this->_getParam('vote_minutes');
			$vote_seconds = $this->_getParam('vote_seconds');

			$starts = $starts_days . "/" . $starts_months . "/" . $starts_years; 
	
			$expires = $expires_days . "/" . $expires_months . "/" . $expires_years;
	
			$interval = ($vote_years * 31557600) + ($vote_months * 2629800) + ($vote_days * 86400) + ($vote_hours * 3600) + ($vote_minutes * 60) + $vote_seconds;
	
			$enabled = $this->_getParam('enabled',0);
			$votefirst = $this->_getParam('votefirst',0);
			$confidential = $this->_getParam('confidential',0);
			$ip = $this->_getParam('ip',0);
			$cookie = $this->_getParam('cookie',0);
			$description = $this->_getParam('description');

			$data = array(
				'id' => $pollId,
				'starts'  => $starts, 
				'expires' => $expires, 
				'interval' => $interval, 
				'enabled' => $enabled, 
				'votefirst' => $votefirst, 
				'confidential' => $confidential, 
				'ip' => $ip, 
				'cookie' => $cookie,
				'description' => $description
			);
			
		    if (false === ($polls->updatePoll($data))) {
		    	throw new Exception('There was a problem saving your comment.');
		    }
			
			$this->_helper->flash->addSuccess('Your survey has been updated successfully.');
			
			$this->_helper->redirector->gotoUrl('/admin/polls/edit/id/' . $pollId);
		
		} else {
			
			$moveup = (int) $this->_getParam('moveup',false);
		
			$movedown = (int) $this->_getParam('movedown',false);
			
			if ((!empty($movedown) || !empty($moveup) && $pollId)) {
			
				$option_id = ($movedown) ? $movedown : $moveup;
				
				$where[] = $pollopts->getAdapter()->quoteInto('id = ?', $option_id);
				$where[] = $pollopts->getAdapter()->quoteInto('poll_id = ?', $pollId);
				
				$ret = $pollopts->fetchRow($where);
				
				if (!empty($movedown)) {
					$pollopts->update(array('order_id' => $ret->order_id + 15), $where);
				} elseif(!empty($moveup)) {
					$pollopts->update(array('order_id' => $ret->order_id - 15), $where);
				}
				unset($ret);
				
				$pollopts->reorderOptions($pollId);
				
			}

			$starts = explode("/", $row->starts);
			$expires = explode("/", $row->expires);

			$years = floor($row->interval / 31557600);
			$months = floor(($row->interval - ($years * 31557600)) / 2629800);
			$days = floor(($row->interval - (($years * 31557600) + ($months * 2629800))) / 86400);
			$hours = floor(($row->interval - (($years * 31557600) + ($months * 2629800) + ($days * 86400))) / 3600);
			$minutes = floor(($row->interval - (($years * 31557600) + ($months * 2629800) + ($days * 86400) + ($hours * 3600))) / 60);
			$seconds = floor(($row->interval - (($years * 31557600) + ($months * 2629800) + ($days * 86400) + ($hours * 3600) + ($minutes * 60))));

			$total_options = count($options);
			
			for ($i = 0; $i < $total_options; $i++) {	

				$order_id = $options[$i]->order_id;  

				$input_option = '<input type="text" name="order_id[' . $options[$i]->id . ']" value="' .$options[$i]->options . '">';

				$link = '';
				if ($i == 0) {
					$link .= '<a href="/admin/polls/edit/id/' . $pollId . '/movedown/' . $options[$i]->id. '">&nbsp;&nbsp;&nbsp;<i class="icon-arrow-down"></i></a>';
				} elseif ($i != 0 && $i != $total_options -1) { 
					$link .= '<a href="/admin/polls/edit/id/' . $pollId . '/moveup/' . $options[$i]->id . '"><i class="icon-arrow-up"></i></a> | <a href="/admin/polls/edit/id/' . $pollId . '/movedown/' . $options[$i]->id . '"><i class="icon-arrow-down"></i></a>';
				} else {
					$link .= '<a href="/admin/polls/edit/id/' . $pollId . '/moveup/' . $options[$i]->id . '">&nbsp;&nbsp;&nbsp;<i class="icon-arrow-up"></i></a>';
				}

				$poll_options[$i] = array(
					'count' => $i+1, 
					'order_id' => $order_id,
					'option' => $options[$i]->options,
					'input_option' => $input_option,
					'anchor' => $link
				);
	
			} 
			
			$this->view->options =  $poll_options;

			$start_day = '<select name="starts_days">';
			for ($i = 1; $i <= 31; $i++) { 
				$day_value =  (strlen ($i) == 1) ? '0' . $i : $i; 
				$selected = ($starts[0] == $i) ? 'selected="selected"' : '';
				$start_day .= '<option value="' . $day_value . '"' . $selected . '>' . $day_value . '</option>';
			} 
			$start_day .= '</select>';
	
			$start_month = '<select name="starts_months">';
			for ($i = 1; $i <= 12; $i++) { 
				$month_value =  (strlen ($i) == 1) ? '0' . $i : $i; 
				$selected = ($starts[1] == $i) ? 'selected="selected"' : '';
				$start_month .= '<option value="' . $month_value . '"' . $selected . '>' . $month_value . '</option>';
			} 
			$start_month .= '</select>';
	
			$current_year = $date = date("Y");
			$start_year = '<select name="starts_years">';
			for ($i = $current_year; $i <= 2050; $i++) { 
				$year_value =  (strlen($i) == 1) ? '200' . $i : $i; 
				$selected = ($starts[2] == '200' . $i) ? 'selected="selected"' : '';
				$start_year .= '<option value="' . $year_value . '"' . $selected . '>' . $year_value . '</option>';
			}
			$start_year .= '</select>';
	
			$expire_day = '<select name="expires_days">';
			for ($i = 1; $i <= 31; $i++) { 
				$day_value =  (strlen ($i) == 1) ? '0' . $i : $i; 
				$selected = ($expires[0] == $i) ? 'selected="selected"' : '';
				$expire_day .= '<option value="' . $day_value . '"' . $selected . '>' . $day_value . '</option>';
			} 
			$expire_day .= '</select>';
	
			$expire_month = '<select name="expires_months">';
			for ($i = 1; $i <= 12; $i++) { 
				$month_value =  (strlen ($i) == 1) ? '0' . $i : $i; 
				$selected = ($expires[1] == $i) ? 'selected="selected"' : '';
				$expire_month .= '<option value="' . $month_value . '"' . $selected . '>' . $month_value . '</option>';
			} 
			$expire_month .= '</select>';
	
			$expire_year = '<select name="expires_years">';
			for ($i = $current_year; $i <= 2050; $i++) { 
				$year_value =  (strlen($i) == 1) ? '200' . $i : $i; 
				$selected = ( $expires[2] == $i ) ? 'selected="selected"' : '';
				$expire_year .= '<option value="' . $year_value . '"' . $selected . '>' . $year_value . '</option>';
			}
			$expire_year .= '</select>';
	
			// When user can vote
	
			$vote_years = '<select name="vote_years">';
			for ($i = 0;$i <= 10; $i++) {
				$selected = ( $years == $i ) ? 'selected="selected"' : '';
				$vote_years .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
	 		}
			$vote_years .= '</select>';
	
			$vote_months = '<select name="vote_months">';
			for ($i = 0;$i <= 12; $i++) {
				$selected = ( $months == $i ) ? 'selected="selected"' : '';
				$vote_months .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
	 		}
			$vote_months .= '</select>';
	
			$vote_days = '<select name="vote_days">';
			for ($i = 0;$i <= 30; $i++) {
				$selected = ( $days == $i ) ? 'selected="selected"' : '';
				$vote_days .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
	 		}
			$vote_days .= '</select>';
	
			$vote_hours = '<select name="vote_hours">';
			for ($i = 0;$i <= 23; $i++) {
				$selected = ( $hours == $i ) ? 'selected="selected"' : '';
				$vote_hours .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
	 		}
			$vote_hours .= '</select>';
	
			$vote_minutes = '<select name="vote_minutes">';
			for ($i = 0;$i <= 59; $i++) {
				$selected = ( $minutes == $i ) ? 'selected="selected"' : '';
				$vote_minutes .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
	 		}
			$vote_minutes .= '</select>';
	
			$vote_seconds = '<select name="vote_seconds">';
			for ($i = 0;$i <= 59; $i++) {
				$selected = ( $minutes == $i ) ? 'selected="selected"' : '';
				$vote_seconds .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
	 		}
			$vote_seconds .= '</select>';
	
			$this->view->data = array(
				'title' => $row->title,
				'poll_id' => $row->id,

				'start_day' => $start_day,
				'start_month' => $start_month,
				'start_year' => $start_year,

				'expire_day' => $expire_day,
				'expire_month' => $expire_month,
				'expire_year' => $expire_year,
			
				'vote_years' => $vote_years,
				'vote_months' => $vote_months,
				'vote_days' => $vote_days,
				'vote_hours' => $vote_hours,
				'vote_minutes' => $vote_minutes,
				'vote_seconds' => $vote_seconds,
		
				'enabled_checked' => ($row->enabled) ? 'checked="checked"' : '',
		
				'confidential_checked' => ($row->confidential) ? 'checked="checked"' : '',
		
				'votefirst_checked' => ($row->votefirst) ? 'checked="checked"' : '',
		
				'ip_checked' => ($row->ip) ? 'checked="checked"' : '',
		
				'cookie_checked' => ($row->cookie) ? 'checked="checked"' : '',
			
				'description' => $row->description
			);
		} 

	} 
	
	public function createAction()
	{
		$request = $this->getRequest();

		$options = $this->_getParam('options');
		
		$title = $this->_getParam('title');
		
		$description = $this->_getParam('description');
		
		if($title && $request->isPost()) {
		
			if (true === empty($title)) {
				throw new Exception('You must type in a poll question to satisfy <i>poll creation criteria</i>...');
			}
			
			$polls = new Default_Model_Polls();
			
			$pollOptions = new Default_Model_PollsOptions();
			
			$data = array(
				'title' => $title,
				'description' => $description,
				'starts' => date("d/m/Y"),
				'expires' => date("d/m/Y"),
				'created' => new Zend_Db_Expr("NOW()"),
				'enabled' => true,
				'votefirst' => true,
				'confidential' => true,
				'ip' => true,
				'cookie' => true
			);
			
			if (false === ($pollId = $polls->insert($data))) {
		    	throw new Exception('There was a problem creating you poll.');
		    }
	
			for ($i = 1; $i <= count($options); $i++) {
				
				$data = array(
					'poll_id' => $pollId,
					'options' => trim(htmlspecialchars(strip_tags($options[$i]))),
					'order_id' => $i
				);
				
				if (false === ($option_id = $pollOptions->insert($data))) {
		    		throw new Exception('There was a problem creating you poll.');
		    	}
				
			}
			
			$this->_helper->redirector->gotoUrl('/admin/polls/edit/id/' . $pollId);
				
		} elseif ($options && $request->isPost()) {
			
			if (!is_numeric($options)) {
				throw new Exception('The value you entered is not an integer between <b>two</b> and <b>' . 6 . '</b>...');
			}
	
			if (floor($options) <= 1 || floor($options) > 6) {
				throw new Exception('Please select a number between <b>two</b> and <b>' . 6 . '</b> to create a poll.');
			}
			
			$this->view->options = (int) $options; 
			
		}
	}
	
	public function deleteAction()
	{			
		if(false === ($pollId = (int) $this->_getParam('id'))) {
			throw new Exception('This method requires a poll identification number.');
		}
		
		$polls = new Default_Model_Polls();

		$pollopts = new Default_Model_PollsOptions();
		
		$pollips = new Default_Model_PollsIp();
		
		$poll = $polls->delete('id = ' . $pollId);
		
		$poll_options = $pollopts->delete('poll_id = ' . $pollId);
		
		$poll_ips = $pollips->delete('poll_id = ' . $pollId);
		
		$this->_helper->redirector->gotoUrl('/admin/polls');
		
	}

}
