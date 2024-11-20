<?php
require_once('../../../wp-load.php');
require_once('./wp-pharmacywire.php');

if (!(WebUser::getUserID())) {
	wp_die(__('You do not have sufficient permissions to access this page.'));
}

if (isset($_GET['invoiceid'])) {
	$invoice_id = htmlentities($_GET['invoiceid'], ENT_QUOTES, "UTF-8");
} else {
	wp_die(__('No invoices to display.'));
	return;
}

function system_mime_type_extensions()
{
	# Returns the system MIME type mapping of MIME types to extensions, as defined in /etc/mime.types (considering the first
	# extension listed to be canonical).
	$out = array();
	$file = fopen('/etc/mime.types', 'r');
	while (($line = fgets($file)) !== false) {
		$line = trim(preg_replace('/#.*/', '', $line));
		if (!$line) {
			continue;
		}
		$parts = preg_split('/\s+/', $line);
		if (count($parts) == 1) {
			continue;
		}
		$type = array_shift($parts);
		if (!isset($out[$type])) {
			$out[$type] = array_shift($parts);
		}
	}
	fclose($file);
	return $out;
}



$patientModel = new Model_Patient();
$getInvoiceDocument = $patientModel->getInvoiceDocument(WebUser::getUserID(), $invoice_id);

$invoices = $getInvoiceDocument->invoices;
$documents = $invoices[$invoice_id]['documents'];

if (count($documents) == 1) {
	$document = $documents[0];
	$extensions = system_mime_type_extensions();
	// default to pdf unless can be determined otherwise
	$file_extension = 'pdf';
	if (isset($extensions[$document['mime_type']][0])) {
		$file_extension = $extensions[$document['mime_type']];
	}

	if ($document['mime_encoding'] === 'raw') {
		$content_length = (int)strlen($document['content']);

		if ($content_length > 0) {
			header('Content-Type: ' . $document['mime_type']);
			header('Content-Disposition: attachment; filename="' . $invoice_id . '.' . $file_extension . '"');
			header('Content-Length: ' . $content_length);

			print $document['content'];
		} else {
			wp_die(__('Invoice is not ready.'));
			return;
		}
	} else {
		wp_die(__("The invoice document is encoded in an unsupported format {$document['mime_encoding']}."));
		return;
	}
} elseif (count($documents) == 0) {
	wp_die(__("Invoice document is not available."));
	return;
} else {
	wp_die(__("Support for multi-page invoices is not supported."));
	return;
}
