<?php

require_once ROOT_DIR . '/sys/DB/LibraryLinkedObject.php';
abstract class DB_LibraryLocationLinkedObject extends DB_LibraryLinkedObject
{
	/**
	 * @return int[]
	 */
	public abstract function getLocations() : array;

	public function okToExport(array $selectedFilters) : bool{
		$okToExport = parent::okToExport($selectedFilters);
		$selectedLibraries = $selectedFilters['locations'];
		foreach ($selectedLibraries as $locationId) {
			if (array_key_exists($locationId, $this->getLocations())) {
				$okToExport = true;
				break;
			}
		}
		return $okToExport;
	}

	public function getLinksForJSON() : array{
		$links = parent::getLinksForJSON();
		$allLocations = Location::getLocationListAsObjects(false);

		$locations = $this->getLocations();

		$links['locations'] = [];
		foreach ($locations as $locationId){
			if (array_key_exists($locationId, $allLocations)) {
				$location = $allLocations[$locationId];
				$links['locations'][$locationId] = $location->code;
			}
		}
		return $links;
	}
}