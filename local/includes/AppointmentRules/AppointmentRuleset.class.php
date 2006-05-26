<?php
class AppointmentRuleset {
	var $rulesetId = false;
	var $rules = array();
	var $messages = array();
	var $errorMessage;

	function AppointmentRuleset($appointmentRulesetId) {
		$this->rulesetId = EnforceType::int($appointmentRulesetId);

		$this->_populate();
	}

	function isValid($appointment) {
		$status = true;
		foreach($this->rules as $rule) {
			$rule->setAppointment($appointment);
			if ($rule->isApplicable()) {
				$s = $rule->isValid();
				if (!$s) {
					$status = false;
					$this->messages[] = $rule->getMessage();
				}
			}
		}
		return $status;
	}

	function getMessage() {
		$message = '<b>'.$this->errorMessage.'</b><ul>';
		foreach($this->messages as $m) {
			$message .= "<li>$m</li>";
		}
		return $message.'</ul>';
	}

	function _populate() {
		$db = new clniDb();
		$sql = "select error_message from appointment_ruleset where appointment_ruleset_id = ".$this->rulesetId;
		$this->errorMessage = $db->getOne($sql);

		$sql = "select * from appointment_rule where appointment_ruleset_id = ".$this->rulesetId;
		$res = $db->execute($sql);
		while($res && !$res->EOF) {

			$class = 'AppointmentRule'.ucfirst($res->fields['type']);
			if (!class_exists($class)) {
				$GLOBALS['loader']->requireOnce('includes/AppointmentRules/'.$class.'.class.php');
			}
			$this->rules[$res->fields['appointment_rule_id']] = new $class($res->fields['label'],unserialize($res->fields['data']));
			$res->MoveNext();
		}

	}
}
?>