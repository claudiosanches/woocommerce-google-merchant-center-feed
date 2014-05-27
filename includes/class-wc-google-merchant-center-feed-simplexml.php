<?php
/**
 * Extends the SimpleXMLElement class to add CDATA element.
 *
 * @since 1.0.0
 */
class WC_Google_Merchant_Center_Feed_SimpleXML extends SimpleXMLElement {

	/**
	 * Add CDATA.
	 *
	 * @param string $string Some string.
	 */
	public function addCData( $string ) {
		$node = dom_import_simplexml( $this );
		$no = $node->ownerDocument;
		$node->appendChild( $no->createCDATASection( $string ) );
	}
}
