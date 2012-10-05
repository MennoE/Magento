<?php
class Kega_Vacancy_VacanciesController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Kega_Vacancy_VacanciesController::indexAction()
	 * Display vacancy module index page
	 */
    public function indexAction()
    {
        if($this->getRequest()->isPost()) {
            $this->_handleFormInput($this->getRequest()->GetPost());
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Vacatures'));
        $this->renderLayout();
    }

    /**
     * Kega_Vacancy_VacanciesController::regionsAction()
     * Redirect to vacancies index
     */
    public function regionsAction()
    {
        $this->_forward('index', 'vacancies');
        return;
    }

    /**
     * Kega_Vacancy_VacanciesController::typesAction()
     * Redirect to vacancies index
     */
    public function typesAction()
    {
        $this->_forward('index', 'vacancies');
        return;
    }

    /**
     * Kega_Vacancy_VacanciesController::detailsAction()
     * Display vacancy details
     */
    public function detailsAction()
    {
        $vacancies = Mage::getModel('vacancy/vacancy');
        $vacancyId = $this->getRequest()->getParam('show', 0);
        $vacancy = $vacancies->getDetails($vacancyId);


		// redirect to module entry point if vacancy isn't active of doesn't exist
		if ($vacancy['vacancy_id'] == null || $vacancy['status'] == 2) {
			$this->_redirect('vacancy');
		}

        Mage::register('vacancy', $vacancy);

        if($this->getRequest()->isPost()) {
            $this->_handleFormInput($this->getRequest()->GetPost());
        }

		// normal way not working due to loadLayoutUpdates
		$head = $this->getLayout()->createBlock('Kega_Meta_Block_Html_Head');
        $head->setKeywords($vacancy['meta_keywords'] . ', ' . $vacancy['store']->getCity());
        $head->setDescription($this->__('Vacancy meta description') . ' ' . $vacancy['comments'] . ' ' . $vacancy['title'] . ' ' . $vacancy['store']->getCity());
        $head->overwriteTitle('Vacature ' . $vacancy['comments'] . ' ' . $vacancy['title'] . ' ' . $vacancy['store']->getCity());

		$update = $this->getLayout()->getUpdate();

		$update->addHandle('default');
		$update->addHandle('vacancy_vacancies_details');

		$this->addActionLayoutHandles();
		$this->loadLayoutUpdates();

		$this->generateLayoutXml()->generateLayoutBlocks();

		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
$breadcrumbs->addCrumb($vacancy['title'] . ' - ' . $vacancy['store']->getCity(), array('label' => $vacancy['title'] . ' - ' . $vacancy['store']->getCity(), 'title' => $vacancy['title'] . ' - ' . $vacancy['store']->getCity()));

		$this->renderLayout();
    }
    public function appliedAction()
    {
    	$this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Vacatures'));
        $this->renderLayout();
    }
    public function fileAction()
    {
        $field = $this->getRequest()->getParam('field');
        $hash = $this->getRequest()->getParam('hash', null);

        $candidates = Mage::getModel('vacancy/vacancycandidate');
        $file = $candidates->getFile($field, $hash);

        header('Content-Disposition: attachment; filename="'.$file.'"');
        echo file_get_contents('./var/vacancies/' . $file);
        die();
    }

    /**
     * Check form input and save data if all data is valid.
     *
     * @param array $values
     */
    private function _handleFormInput($values)
    {
        $errors = array();
        $values = array();

        $candidates = Mage::getModel('vacancy/vacancycandidate');
        $vacancies = Mage::getModel('vacancy/vacancy');
        $vacancyId = $this->getRequest()->getParam('show', 0);
        $vacancy = $vacancies->getDetails($vacancyId);
        $formType = $this->getRequest()->getParam('vacancy-type', 0);

        $required = array('first-name', 'last-name', 'initials', 'street', 'number', 'postcode', 'city', 'email');

        $allFields = array(
                'gender', 'initials', 'first-name', 'last-name',
                'street', 'number', 'number-addition', 'city',
                'postcode', 'country', 'nationality', 'birth-date',
                'email', 'phone', 'phone-mobile', 'cv-upload',
                'motivation', 'photo-upload',
                'training-1', 'training-1-start', 'training-1-end',
                'training-1-completed', 'training-2', 'training-2-start',
                'training-2-end', 'training-2-completed',
                'experience-1-company', 'experience-1-start', 'experience-1-end',
                'experience-1-function', 'experience-2-company', 'experience-2-start',
                'experience-2-end', 'experience-2-function'
            );

        if($formType == 'type-3') {
            $required = array(
                'apply-for-function','preferred-store-1','available-from',
                'first-name','last-name', 'initials',
                'street', 'number', 'postcode', 'city', 'phone', 'email',
                'motivation'
            );

            array_push($allFields,
                'apply-for-function', 'preferred-store-1', 'preferred-store-2',
                'preferred-store-3', 'available-from', 'available-days'
            );
        }

//       $allFields = array('initials', 'first-name', 'last-name', 'gender', 'email', 'postcode', 'number', 'number-addition', 'street', 'city', 'country',
//       'apply-for-function', 'preferred-store-1', 'preferred-store-2', 'preferred-store-3', 'available-from', 'available-days', 'birth-date', 'nationality', 'phone', 'phone-mobile',
//       'training-1', 'training-1-start', 'training-1-end', 'training-1-completed', 'training-2', 'training-2-start', 'training-2-end', 'training-2-completed',
//       'experience-1-company', 'experience-1-start', 'experience-1-end', 'experience-1-function', 'experience-2-company', 'experience-2-start', 'experience-2-end', 'experience-2-function',
//       'properties-strong', 'properties-weak', 'motivation', 'cv-upload', 'motivation-upload', 'photo-upload');

        $fileFields = array('cv-upload', 'motivation-upload', 'photo-upload');
        $storeFields = array('preferred-store-1', 'preferred-store-2', 'preferred-store-3');

        foreach($required as $field) {
            if(empty($_POST[$field])) {
                $errors['required'][] = $field;
            }
        }

        // check file extensions for uploaded files
        if(isset($_FILES['cv-upload']) && !$candidates->checkFileExtension($_FILES['cv-upload']['name'])) {
            $errors['invalid'] = 'cv-upload';
        }
        if(isset($_FILES['motivation-upload']) && !$candidates->checkFileExtension($_FILES['motivation-upload']['name'])) {
            $errors['motivation-upload'] = 'motivation-upload';
        }
        if(isset($_FILES['photo-upload']) && !$candidates->checkFileExtension($_FILES['photo-upload']['name'])) {
            $errors['photo-upload'] = 'photo-upload';
        }

        Mage::register('vacancy-apply-errors', $errors);
        Mage::register('vacancy-apply-values', $values);

        if(empty($errors)) {

            $candidate = array('vacancy_id' => $vacancyId);

            foreach($allFields as $field) {
                $candidate[$field] = $this->getRequest()->getPost($field);
            }

            // overwrite some 'special' fields
            if(!empty($candidate['available-days'])) {
                $candidate['available-days'] = implode(', ', $candidate['available-days']);
            } else {
                $candidate['available-days'] = '';
            }

            $candidateId = $candidates->insert($candidate);

            $files = array();
            if(isset($_FILES['cv-upload'])) {
				$_FILES['cv-upload']['tmp_name'] = $candidates->addFile('cv',$candidateId, $_FILES['cv-upload']);
				$files[] = $_FILES['cv-upload'];
            }
            if(isset($_FILES['motivation-upload'])) {
				$_FILES['motivation-upload']['tmp_name'] = $candidates->addFile('motivation',$candidateId, $_FILES['motivation-upload']);
				$files[] = $_FILES['motivation-upload'];
            }
            if(isset($_FILES['photo-upload'])) {
				$_FILES['photo-upload']['tmp_name'] = $candidates->addFile('photo',$candidateId, $_FILES['photo-upload']);
				$files[] = $_FILES['photo-upload'];
            }

            $body = $this->getLayout()->createBlock('Kega_Vacancy_Block_Mail', 'vacancy-mail')
                                      ->setTemplate('vacancy/mail.phtml')
                                      ->setData('candidate_id', $candidateId)
                                      ->setData('fields', $allFields)
                                      ->setData('filefields', $fileFields)
                                      ->setData('storefields', $storeFields)
                                      ->toHtml();

            // send mail
            try{
                /**
                 * @todo get $to from config..
                 */
                $to = Mage::getStoreConfig('trans_email/ident_custom1/email');
                $subject = 'Reactie op vacature';
                $from = Mage::getStoreConfig('trans_email/ident_custom1/name') .
                        ' <' . Mage::getStoreConfig('trans_email/ident_general/email') . '> ';
                $sender = $this->getRequest()->getPost('first-name') . ' ' . $this->getRequest()->getPost('last-name') .
                        ' <' . $this->getRequest()->getPost('email') . '> ';

                $mailTemplate = Mage::getModel('core/email_template')
                        ->setDesignConfig(array('area'  => 'frontend',
						                        'store' => $store))
                        ->setReplyTo($sender);

				foreach ($files as $file) {
					$at				= $mailTemplate->getMail()->createAttachment(file_get_contents($file['tmp_name']));
					$at->type		= $file['type'];
					$at->filename	= $file['name'];
				}

                $mailTemplate->sendTransactional(
                            'vacancy_email_notify',
                            'general',
                            $to,
                            null,
                            array('body' => $body)
                        );
            } catch (Exception $e) {
                error_log('sending vacancy notifacation failed: ' . $e->getMessage());
            }

            /*
             * Send customer mail notification for vacancy apply
             *
             */
            $store = Mage::app()->getStore()->getStoreId();

            // Transactional Email Template's ID
            $templateId = Mage::getStoreConfig('customer/vacancy_settings/email_template', $store);

            // Set sender information
            $senderName = Mage::getStoreConfig('trans_email/ident_general/name', $store);
            $senderEmail = Mage::getStoreConfig('trans_email/ident_general/email', $store);
            $sender = array('name' => $senderName,
                        'email' => $senderEmail);

            // Set recepient information
            $recepientName = htmlspecialchars($candidate['first-name']);
            $recepientEmail = htmlspecialchars($candidate['email']);

            $translate  = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(true);

            try {
                // Send Transactional Email
                $mailTemplate = Mage::getModel('core/email_template')
                    ->setDesignConfig(array('area'  => 'frontend',
											'store' => $store));

				/**
				 * If needed, we could activate attachments to the candidate also.
				foreach ($files as $file) {
					$at				= $mailTemplate->getMail()->createAttachment(file_get_contents($file['tmp_name']));
					$at->type		= $file['type'];
					$at->filename	= $file['name'];
				}
				*/

				$mailTemplate->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, false, $store);

            } catch (Exception $e) {
                    Mage::log('Customer Vacancy notification cannot be sent, please notice: ' . $e->getMessage());
            }

            $this->_redirect('vacatures/vacancies/applied');
            return;
        }
    }
}