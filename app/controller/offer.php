<?php

/*
 * gsFrontend
 * Copyright (C) 2011 Gedankengut GbR Häuser & Sirin <support@gsales.de>
 * 
 * This file is part of gsFrontend.
 * 
 * gsFrontend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * gsFrontend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with gsFrontend. If not, see <http://www.gnu.org/licenses/>.
 */

class FRONTEND_CONTROLLER_OFFER extends FRONTEND_CONTROLLER {

	public function __construct(){
		parent::__construct();
	}
	
	public function indexAction(){
		
		// rechnungen des kunden auslesen
		$objDataOffer = new GSALES_DATA_OFFER();
		$arrayOfObjOffer = $objDataOffer->getOffersByCustomerId($this->objUserAuth->getCustomerId());
		$this->objSmarty->assignByRef('offers', $arrayOfObjOffer);
		$this->objSmarty->assign('payment_paypal', PAYPAL_ENABLE);
		$this->objSmarty->assign('payment_sofort', SOFORTU_ENABLE);
		
	}

	public function acceptAction(){
		
		$arrUserRequest = $this->getUserRequest();
		
		// no invoice id given
		if (false == isset($arrUserRequest['params']['0']) || false == is_numeric($arrUserRequest['params']['0'])){
			$this->redirectTo('offer');
			return;
		}
		
		$objDataInvoice = new GSALES_DATA_OFFER();
		$objOffer = $objDataInvoice->getOfferById($arrUserRequest['params']['0'], $this->objUserAuth->getCustomerId(), true);
		
		// offer does not exist (or belongs to another customer)
		if (false == $objOffer){
			$this->setMessage('Ungültiger Aufruf', 'error');
			$this->redirectTo('offer');
			return;
		}
		
		// offer is already accepted
		if ($objOffer->getStatusId() != 0){
			$this->setMessage('Das von Ihnen gewählte Angebot ist nicht offen', 'error');
			$this->redirectTo('offer');
			return;
		}
		
		$this->objSmarty->assignByRef('offer', $objOffer);
		//$this->objSmarty->assign('payment_paypal', PAYPAL_ENABLE);
		
	}

	public function acceptnowAction(){
		
		$arrUserRequest = $this->getUserRequest();
		
		// no invoice id given
		if (false == isset($arrUserRequest['params']['0']) || false == is_numeric($arrUserRequest['params']['0'])){
			$this->redirectTo('offer');
			return;
		}
		
		$objDataOffer = new GSALES_DATA_OFFER();
		$objOffer = $objDataOffer->getOfferById($arrUserRequest['params']['0'], $this->objUserAuth->getCustomerId(), true);
		
		// offer does not exist (or belongs to another customer)
		if (false == $objOffer){
			$this->setMessage('Ungültiger Aufruf', 'error');
			$this->redirectTo('offer');
			return;
		}
		
		// offer is already accepted
		if ($objOffer->getStatusId() != 0){
			$this->setMessage('Das von Ihnen gewählte Angebot ist nicht offen', 'error');
			$this->redirectTo('offer');
			return;
		}

		if(time() > strtotime($objOffer->getValidUntil())) {
			$this->setMessage('Das von Ihnen gewählte Angebot ist nicht mehr gültig. Bitte kontaktieren Sie uns.', 'error');
			$this->redirectTo('offer');
			return;
		}
		
		$objectReturn = $objDataOffer->setOfferStateAccepted($arrUserRequest['params']['0']);
		
		if (false == $objectReturn){
			$this->setMessage('Es ist ein Fehler aufgetreten. Bitte setzen Sie sich mit uns in Verbindung.', 'error');
			$this->redirectTo('offer');
			return;
		} else {

			$objDataCustomer = new GSALES_DATA_CUSTOMER();
			$objCustomer = $objDataCustomer->getCustomerById($this->objUserAuth->getCustomerId());

			$arrPDF = $objDataOffer->getOfferPDFFile($arrUserRequest['params']['0'], $this->objUserAuth->getCustomerId());

			$objMailer = new FRONTEND_MAILER();

			// Infomail to admin

			if(defined('MAIL_TO')) {
				$objMailer->FromName =  trim($objCustomer->getFirstname() . ' ' . $objCustomer->getLastname());
				$objMailer->From = $objCustomer->getEmail();
				$objMailer->AddReplyTo($objCustomer->getEmail(), trim($objCustomer->getFirstname() . ' ' . $objCustomer->getLastname()));
				$objMailer->Subject = 'gS Kundenfrontend: Angebot  "'.$objOffer->getOfferNo().'" wurde angenommen.';
				$objMailer->Body = "Hallo,\n\ndas Angebot ".$objOffer->getOfferNo()." wurde soeben verbindlich von ".$objMailer->FromName." angenommen.\n\nEnde der automatischen Nachricht.";
				$objMailer->AddAddress(MAIL_TO);
				$objMailer->AddStringAttachment($arrPDF['content'], $objOffer->getOfferNo().'.pdf');

				$booCheck = $objMailer->Send();

				$objMailer->ClearAllRecipients();
				$objMailer->ClearAttachments();
			}


			// Confirmation to Customer

			if($objCustomer->getEmail()) {
				$objMailer->FromName =  trim($objCustomer->getFirstname() . ' ' . $objCustomer->getLastname());
				$objMailer->From = $objCustomer->getEmail();
				$objMailer->AddReplyTo($objCustomer->getEmail(), trim($objCustomer->getFirstname() . ' ' . $objCustomer->getLastname()));
				$objMailer->Subject = 'Auftragsbestätigung Angebot  "'.$objOffer->getOfferNo().'"';
				$objMailer->Body = "Hallo ".$objMailer->FromName.",\n\nvielen Dank für Ihr entgegengebrachtes Vertrauen. Hiermit bestätigen wir Ihre Annahme des Angebotes ".$objOffer->getOfferNo()." datiert auf den ".date("d.m.Y", strtotime($objOffer->getCreated())).". Wir werden uns für den weiteren Ablauf schnellstmöglich mit Ihnen in Verbindung setzen. Im Anhang befindet sich das Angebot für Ihre Unterlagen.\n\nAngebotsannahme: ".date("d.m.Y - H:i", time())." Uhr\n\n\nBeste Grüße\nIhr ... TEAM\n\n\nFirma\nInh. Max Mustermann\n\nMusterstr. 123\n11111 Musterstadt\n\nEmail max@mustermann.de\nFon +49 (0)11-1111111-0\nFax +49 (0)11-11111111-9\n\nUSt-IdNr.: DE1111111111";
				$objMailer->AddAddress($objCustomer->getEmail());
				$objMailer->AddBCC(MAIL_TO);
				$objMailer->AddStringAttachment($arrPDF['content'], $objOffer->getOfferNo().'.pdf');

				$booCheck = $objMailer->Send();

				$objMailer->ClearAllRecipients();
				$objMailer->ClearAttachments();
			}


			// Final redirect
			$this->redirectTo('offer/acceptsuccess');
		}

	}

	public function acceptsuccessAction(){
		// displays only according template
	}

	public function acceptfailureAction(){
		// displays only according template
	}
	
	public function pdfAction(){
		
		$this->setSmartyOutput(false);
		$arrUserRequest = $this->getUserRequest();
		
		if (false == isset($arrUserRequest['params']['0']) || false == is_numeric($arrUserRequest['params']['0'])){
			$this->redirectTo('offer'); // check for invoice id to get pdf for
			return;
		}
		
		$objDataOffer = new GSALES_DATA_OFFER();
		$arrPDF = $objDataOffer->getOfferPDFFile($arrUserRequest['params']['0'], $this->objUserAuth->getCustomerId());
		
		if (false == $arrPDF){
			$this->redirectTo('offer');
			return;
		}
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="'.$arrPDF['filename'].'"');
		echo $arrPDF['content'];		
		
	}
		
	
}