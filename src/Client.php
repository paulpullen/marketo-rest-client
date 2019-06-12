<?php
/*
 * This file is part of the Marketo REST API Client package.
 *
 * (c) 2014 Daniel Chesterton
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CSD\Marketo;

// Guzzle
use CommerceGuys\Guzzle\Plugin\Oauth2\Oauth2Plugin;
use CSD\Marketo\Response\AddCustomActivitiesResponse;
use CSD\Marketo\Response\GetLeadChanges;
use CSD\Marketo\Response\GetPagingToken;
use Guzzle\Common\Collection;
use Guzzle\Service\Client as GuzzleClient;
use Guzzle\Service\Description\ServiceDescription;

// Response classes
use CSD\Marketo\Response\AddOrRemoveLeadsToListResponse;
use CSD\Marketo\Response\AssociateLeadResponse;
use CSD\Marketo\Response\CreateOrUpdateLeadsResponse;
use CSD\Marketo\Response\GetCampaignResponse;
use CSD\Marketo\Response\GetCampaignsResponse;
use CSD\Marketo\Response\GetLeadResponse;
use CSD\Marketo\Response\GetLeadPartitionsResponse;
use CSD\Marketo\Response\GetLeadsResponse;
use CSD\Marketo\Response\GetListResponse;
use CSD\Marketo\Response\GetListsResponse;
use CSD\Marketo\Response\IsMemberOfListResponse;


/**
 * Guzzle client for communicating with the Marketo.com REST API.
 *
 * @link http://developers.marketo.com/documentation/rest/
 *
 * @author Daniel Chesterton <daniel@chestertondevelopment.com>
 */
class Client extends GuzzleClient
{
    /**
     * @var array
     */
    private $marketoObjects = array(
        'Leads' => 'leads',
        'Companies' => 'companies',
        'Opportunities' => 'opportunities',
        'Opportunities Roles' => 'opportunities/roles',
        'Sales Persons' => 'salespersons'
    );

    /**
     * {@inheritdoc}
     */
    public static function factory($config = array())
    {
        $default = array(
            'url' => false,
            'munchkin_id' => false,
            'version' => 1,
            'bulk' => false
        );

        $required = array('client_id', 'client_secret', 'version');
        $config = Collection::fromConfig($config, $default, $required);

        $url = $config->get('url');

        if (!$url) {
            $munchkin = $config->get('munchkin_id');

            if (!$munchkin) {
                throw new \Exception('Must provide either a URL or Munchkin code.');
            }

            $url = sprintf('https://%s.mktorest.com', $munchkin);
        }

        $grantType = new Credentials($url, $config->get('client_id'), $config->get('client_secret'));
        $auth = new Oauth2Plugin($grantType);

        if ($config->get('bulk') === true) {
            $restUrl = sprintf('%s/bulk/v%d', rtrim($url, '/'), $config->get('version'));
        } else {
            $restUrl = sprintf('%s/rest/v%d', rtrim($url, '/'), $config->get('version'));
        }

        $client = new self($restUrl, $config);
        $client->addSubscriber($auth);
        $client->setDescription(ServiceDescription::factory(__DIR__ . '/service.json'));
        $client->setDefaultOption('headers/Content-Type', 'application/json');

        return $client;
    }

    /**
     * Creates a bulk lead extract job
     *
     * @see https://developers.marketo.com/rest-api/bulk-extract/bulk-lead-extract/
     *
     * @param array $args ['format' => 'CSV', 'fields' => [], 'columnHeaderNames' => [], 'filter' => []]
     *
     * @return Response
     */
    public function createBulkLeadExtractJob($args)
    {
        return $this->getResult('createBulkLeadExtractJob', $args);
    }

    /**
     * Enqueues a bulk lead extract job
     *
     * @see https://developers.marketo.com/rest-api/bulk-extract/bulk-lead-extract/
     *
     * @param string $exportId
     * @throws \Exception
     * @return Response
     */
    public function enqueueBulkLeadExtractJob($exportId)
    {
        if(empty($exportId)) {
            throw new \Exception('Missing exportId!');
        }

        return $this->getResult('enqueueBulkLeadExtractJob', ['exportId' => $exportId]);
    }

    /**
     * Gets a bulk lead extract job's status
     *
     * @see https://developers.marketo.com/rest-api/bulk-extract/bulk-lead-extract/
     *
     * @param string $exportId
     * @return Response
     * @throws \Exception
     */
    public function getBulkLeadExtractJobStatus($exportId)
    {
        if(empty($exportId)) {
            throw new \Exception('Missing exportId!');
        }

        return $this->getResult('getBulkLeadExtractJobStatus', ['exportId' => $exportId]);
    }

    /**
     * Gets the results file of a bulk lead extract job
     *
     * @see https://developers.marketo.com/rest-api/bulk-extract/bulk-lead-extract/
     *
     * @param string $exportId
     * @param bool $returnRaw
     * @return Response
     * @throws \Exception
     */
    public function getBulkLeadExtractJobResults($exportId, $returnRaw = true)
    {
        if(empty($exportId)) {
            throw new \Exception('Missing exportId!');
        }

        return $this->getResult('getBulkLeadExtractJobResults', ['exportId' => $exportId], false, $returnRaw);
    }

    /**
     * Creates a bulk activities extract job
     *
     * @see https://developers.marketo.com/rest-api/bulk-extract/bulk-lead-extract/
     *
     * @param array $args ['format' => 'CSV', 'fields' => [], 'columnHeaderNames' => [], 'filter' => []]
     *
     * @return Response
     */
    public function createBulkActivitiesExtractJob($args)
    {
        return $this->getResult('createBulkActivitiesExtractJob', $args);
    }

    /**
     * Enqueues a bulk activities extract job
     *
     * @see https://developers.marketo.com/rest-api/bulk-extract/bulk-lead-extract/
     *
     * @param string $exportId
     * @throws \Exception
     * @return Response
     */
    public function enqueueBulkActivitiesExtractJob($exportId)
    {
        if(empty($exportId)) {
            throw new \Exception('Missing exportId!');
        }

        return $this->getResult('enqueueBulkActivitiesExtractJob', ['exportId' => $exportId]);
    }

    /**
     * Gets a bulk activities extract job's status
     *
     * @see https://developers.marketo.com/rest-api/bulk-extract/bulk-lead-extract/
     *
     * @param string $exportId
     * @return Response
     * @throws \Exception
     */
    public function getBulkActivitiesExtractJobStatus($exportId)
    {
        if(empty($exportId)) {
            throw new \Exception('Missing exportId!');
        }

        return $this->getResult('getBulkActivitiesExtractJobStatus', ['exportId' => $exportId]);
    }

    /**
     * Gets the results file of a bulk activities extract job
     *
     * @see https://developers.marketo.com/rest-api/bulk-extract/bulk-lead-extract/
     *
     * @param string $exportId
     * @param bool $returnRaw
     * @return Response
     * @throws \Exception
     */
    public function getBulkActivitiesExtractJobResults($exportId, $returnRaw = true)
    {
        if(empty($exportId)) {
            throw new \Exception('Missing exportId!');
        }

        return $this->getResult('getBulkActivitiesExtractJobResults', ['exportId' => $exportId], false, $returnRaw);
    }


    /**
     * Import Leads via file upload
     *
     * @param array $args - Must contain 'format' and 'file' keys
     *     e.g. array( 'format' => 'csv', 'file' => '/full/path/to/filename.csv'
     *
     * @link http://developers.marketo.com/documentation/rest/import-lead/
     *
     * @return array
     *
     * @throws \Exception
     */
    public function importLeadsCsv($args)
    {
        if (!is_readable($args['file'])) {
            throw new \Exception('Cannot read file: ' . $args['file']);
        }

        if (empty($args['format'])) {
            $args['format'] = 'csv';
        }

        return $this->getResult('importLeadsCsv', $args);
    }

    /**
     * Get status of an async Import Lead file upload
     *
     * @param int $batchId
     *
     * @throws \Exception
     *
     * @link http://developers.marketo.com/documentation/rest/get-import-lead-status/
     *
     * @return array
     */
    public function getBulkUploadStatus($batchId)
    {
        if (empty($batchId) || !is_int($batchId)) {
            throw new \Exception('Invalid $batchId provided in ' . __METHOD__);
        }

        return $this->getResult('getBulkUploadStatus', array('batchId' => $batchId));
    }

    /**
     * Get failed lead results from an Import Lead file upload
     *
     * @param int $batchId
     *
     * @throws \Exception
     *
     * @link http://developers.marketo.com/documentation/rest/get-import-failure-file/
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function getBulkUploadFailures($batchId)
    {
        if (empty($batchId) || !is_int($batchId)) {
            throw new \Exception('Invalid $batchId provided in ' . __METHOD__);
        }

        return $this->getResult('getBulkUploadFailures', array('batchId' => $batchId));
    }

    /**
     * Get warnings from Import Lead file upload
     *
     * @param int $batchId
     *
     * @throws \Exception
     *
     * @link http://developers.marketo.com/documentation/rest/get-import-warning-file/
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function getBulkUploadWarnings($batchId)
    {
        if (empty($batchId) || !is_int($batchId)) {
            throw new \Exception('Invalid $batchId provided in ' . __METHOD__);
        }

        return $this->getResult('getBulkUploadWarnings', array('batchId' => $batchId));
    }

    /**
     * Calls the CreateOrUpdateLeads command with the given action.
     *
     * @param string $action
     * @param array $leads
     * @param string $lookupField
     * @param array $args
     * @param bool $returnRaw
     *
     * @see Client::createLeads()
     * @see Client::createOrUpdateLeads()
     * @see Client::updateLeads()
     * @see Client::createDuplicateLeads()
     *
     * @link http://developers.marketo.com/documentation/rest/createupdate-leads/
     *
     * @return CreateOrUpdateLeadsResponse
     */
    private function createOrUpdateLeadsCommand($action, $leads, $lookupField, $args, $returnRaw = false)
    {
        $args['input'] = $leads;
        $args['action'] = $action;

        if (isset($lookupField)) {
            $args['lookupField'] = $lookupField;
        }

        return $this->getResult('createOrUpdateLeads', $args, false, $returnRaw);
    }

    /**
     * Only update the given opportunity roles.
     *
     * @param array $opportunitiesRoles Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Opportunities/syncOpportunityRolesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function updateOpportunitiesRoles($opportunitiesRoles, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Opportunities Roles', 'updateOnly', $opportunitiesRoles, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Only create the given opportunity roles.
     *
     * @param array $opportunitiesRoles Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Opportunities/syncOpportunityRolesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function createOpportunitiesRoles($opportunitiesRoles, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Opportunities Roles', 'createOnly', $opportunitiesRoles, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Create or update the given opportunity roles.
     *
     * @param array $opportunitiesRoles Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Opportunities/syncOpportunityRolesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function createOrUpdateOpportunitiesRoles($opportunitiesRoles, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Opportunities Roles', 'createOrUpdate', $opportunitiesRoles, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Only update the given opportunities.
     *
     * @param array $opportunities Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Opportunities/syncOpportunitiesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function updateOpportunities($opportunities, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Opportunities', 'updateOnly', $opportunities, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Only create the given opportunities.
     *
     * @param array $opportunities Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Opportunities/syncOpportunitiesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function createOpportunities($opportunities, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Opportunities', 'createOnly', $opportunities, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Create or update the given opportunities.
     *
     * @param array $opportunities Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Opportunities/syncOpportunitiesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function createOrUpdateOpportunities($opportunities, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Opportunities', 'createOrUpdate', $opportunities, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Only update the given companies.
     *
     * @param array $companies Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Companies/syncCompaniesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function updateCompanies($companies, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Companies', 'updateOnly', $companies, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Only create the given companies.
     *
     * @param array $companies Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Companies/syncCompaniesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function createCompanies($companies, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Companies', 'createOnly', $companies, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Create or update the given companies.
     *
     * @param array $companies Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Companies/syncCompaniesUsingPOST
     *
     * @return GetLeadsResponse
     */
    public function createOrUpdateCompanies($companies, $dedupeBy = 'dedupeFields', $args = array(), $returnRaw = false)
    {
        return $this->createOrUpdateObjects('Companies', 'createOrUpdate', $companies, $dedupeBy, $args, $returnRaw);
    }

    /**
     * Generic method to create or update Marketo objects.
     *
     * @param string $objectName
     * @param string $action Should be createOnly, updateOnly, or createOrUpdate.
     * @param array $records Array of arrays.
     * @param string $dedupeBy
     * @param array $args
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @return GetLeadsResponse
     */
    private function createOrUpdateObjects($objectName, $action, $records, $dedupeBy, $args = array(), $returnRaw = false)
    {
        if (!isset($this->marketoObjects[$objectName])) {
            throw new \Exception('createOrUpdate() Expected parameter $objectName, to be a valid Marketo object ' . "but $objectName provided");
        };

        $args['objectName'] = $this->marketoObjects[$objectName];
        $args['action'] = $action;
        $args['input'] = $records;
        $args['dedupeBy'] = $dedupeBy;

        return $this->getResult('createOrUpdateObject', $args, false, $returnRaw);
    }

    /**
     * Create the given leads.
     *
     * @param array $leads
     * @param string $lookupField
     * @param array $args
     * @see Client::createOrUpdateLeadsCommand()
     *
     * @link http://developers.marketo.com/documentation/rest/createupdate-leads/
     *
     * @return CreateOrUpdateLeadsResponse
     */
    public function createLeads($leads, $lookupField = null, $args = array())
    {
        return $this->createOrUpdateLeadsCommand('createOnly', $leads, $lookupField, $args);
    }

    /**
     * Update the given leads, or create them if they do not exist.
     *
     * @param array $leads
     * @param string $lookupField
     * @param array $args
     * @see Client::createOrUpdateLeadsCommand()
     *
     * @link http://developers.marketo.com/documentation/rest/createupdate-leads/
     *
     * @return CreateOrUpdateLeadsResponse
     */
    public function createOrUpdateLeads($leads, $lookupField = null, $args = array())
    {
        return $this->createOrUpdateLeadsCommand('createOrUpdate', $leads, $lookupField, $args);
    }

    /**
     * Update the given leads.
     *
     * @param array $leads
     * @param string $lookupField
     * @param array $args
     * @see Client::createOrUpdateLeadsCommand()
     *
     * @link http://developers.marketo.com/documentation/rest/createupdate-leads/
     *
     * @return CreateOrUpdateLeadsResponse
     */
    public function updateLeads($leads, $lookupField = null, $args = array())
    {
        return $this->createOrUpdateLeadsCommand('updateOnly', $leads, $lookupField, $args);
    }

    /**
     * Create duplicates of the given leads.
     *
     * @param array $leads
     * @param string $lookupField
     * @param array $args
     * @see Client::createOrUpdateLeadsCommand()
     *
     * @link http://developers.marketo.com/documentation/rest/createupdate-leads/
     *
     * @return CreateOrUpdateLeadsResponse
     */
    public function createDuplicateLeads($leads, $lookupField = null, $args = array())
    {
        return $this->createOrUpdateLeadsCommand('createDuplicate', $leads, $lookupField, $args);
    }

    /**
     * Get multiple lists.
     *
     * @param int|array $ids Filter by one or more IDs
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-multiple-lists/
     *
     * @return GetListsResponse
     */
    public function getLists($ids = null, $args = array(), $returnRaw = false)
    {
        if ($ids) {
            $args['id'] = $ids;
        }

        return $this->getResult('getLists', $args, is_array($ids), $returnRaw);
    }

    /**
     * Get a list by ID.
     *
     * @param int $id
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-list-by-id/
     *
     * @return GetListResponse
     */
    public function getList($id, $args = array(), $returnRaw = false)
    {
        $args['id'] = $id;

        return $this->getResult('getList', $args, false, $returnRaw);
    }

    /**
     * Get multiple leads by filter type.
     *
     * @param string $filterType One of the supported filter types, e.g. id, cookie or email. See Marketo's documentation for all types.
     * @param string $filterValues Comma separated list of filter values
     * @param array $fields Array of field names to be returned in the response
     * @param string $nextPageToken
     * @param bool $returnRaw
     * @link http://developers.marketo.com/documentation/rest/get-multiple-leads-by-filter-type/
     *
     * @return GetLeadsResponse
     */
    public function getLeadsByFilterType($filterType, $filterValues, $fields = array(), $nextPageToken = null, $returnRaw = false)
    {
        $args['filterType'] = $filterType;
        $args['filterValues'] = $filterValues;

        if ($nextPageToken) {
            $args['nextPageToken'] = $nextPageToken;
        }

        if (count($fields)) {
            $args['fields'] = implode(',', $fields);
        }

        return $this->getResult('getLeadsByFilterType', $args, false, $returnRaw);
    }

    /**
     * Get a lead by filter type.
     *
     * Convenient method which uses {@link http://developers.marketo.com/documentation/rest/get-multiple-leads-by-filter-type/}
     * internally and just returns the first lead if there is one.
     *
     * @param string $filterType One of the supported filter types, e.g. id, cookie or email. See Marketo's documentation for all types.
     * @param string $filterValue The value to filter by
     * @param array $fields Array of field names to be returned in the response
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-multiple-leads-by-filter-type/
     *
     * @return GetLeadResponse
     */
    public function getLeadByFilterType($filterType, $filterValue, $fields = array(), $returnRaw = false)
    {
        $args['filterType'] = $filterType;
        $args['filterValues'] = $filterValue;

        if (count($fields)) {
            $args['fields'] = implode(',', $fields);
        }

        return $this->getResult('getLeadByFilterType', $args, false, $returnRaw);
    }

    /**
     * Get lead partitions.
     *
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-lead-partitions/
     *
     * @return GetLeadPartitionsResponse
     */
    public function getLeadPartitions($args = array(), $returnRaw = false)
    {
        return $this->getResult('getLeadPartitions', $args, false, $returnRaw);
    }

    /**
     * Get multiple leads by list ID.
     *
     * @param int $listId
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-multiple-leads-by-list-id/
     *
     * @return GetLeadsResponse
     */
    public function getLeadsByList($listId, $args = array(), $returnRaw = false)
    {
        $args['listId'] = $listId;

        return $this->getResult('getLeadsByList', $args, false, $returnRaw);
    }

    /**
     * Get a lead by ID.
     *
     * @param int $id
     * @param array $fields
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-lead-by-id/
     *
     * @return GetLeadResponse
     */
    public function getLead($id, $fields = null, $args = array(), $returnRaw = false)
    {
        $args['id'] = $id;

        if (is_array($fields)) {
            $args['fields'] = implode(',', $fields);
        }

        return $this->getResult('getLead', $args, false, $returnRaw);
    }

    /**
     * Check if a lead is a member of a list.
     *
     * @param int $listId List ID
     * @param int|array $id Lead ID or an array of Lead IDs
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/member-of-list/
     *
     * @return IsMemberOfListResponse
     */
    public function isMemberOfList($listId, $id, $args = array(), $returnRaw = false)
    {
        $args['listId'] = $listId;
        $args['id'] = $id;

        return $this->getResult('isMemberOfList', $args, is_array($id), $returnRaw);
    }

    /**
     * Get a campaign by ID.
     *
     * @param int $id
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-campaign-by-id/
     *
     * @return GetCampaignResponse
     */
    public function getCampaign($id, $args = array(), $returnRaw = false)
    {
        $args['id'] = $id;

        return $this->getResult('getCampaign', $args, false, $returnRaw);
    }

    /**
     * Get campaigns.
     *
     * @param int|array $ids A single Campaign ID or an array of Campaign IDs
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-multiple-campaigns/
     *
     * @return GetCampaignsResponse
     */
    public function getCampaigns($ids = null, $args = array(), $returnRaw = false)
    {
        if ($ids) {
            $args['id'] = $ids;
        }

        return $this->getResult('getCampaigns', $args, is_array($ids), $returnRaw);
    }

    /**
     * Add one or more leads to the specified list.
     *
     * @param int $listId List ID
     * @param int|array $leads Either a single lead ID or an array of lead IDs
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/add-leads-to-list/
     *
     * @return AddOrRemoveLeadsToListResponse
     */
    public function addLeadsToList($listId, $leads, $args = array(), $returnRaw = false)
    {
        $args['listId'] = $listId;
        $args['id'] = (array)$leads;

        return $this->getResult('addLeadsToList', $args, true, $returnRaw);
    }

    /**
     * Remove one or more leads from the specified list.
     *
     * @param int $listId List ID
     * @param int|array $leads Either a single lead ID or an array of lead IDs
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/remove-leads-from-list/
     *
     * @return AddOrRemoveLeadsToListResponse
     */
    public function removeLeadsFromList($listId, $leads, $args = array(), $returnRaw = false)
    {
        $args['listId'] = $listId;
        $args['id'] = (array)$leads;

        return $this->getResult('removeLeadsFromList', $args, true, $returnRaw);
    }

    /**
     * Delete one or more leads
     *
     * @param int|array $leads Either a single lead ID or an array of lead IDs
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/delete-lead/
     *
     * @return \CSD\Marketo\Response\DeleteLeadResponse
     */
    public function deleteLead($leads, $args = array(), $returnRaw = false)
    {
        $args['id'] = (array)$leads;

        return $this->getResult('deleteLead', $args, true, $returnRaw);
    }

    /**
     * Trigger a campaign for one or more leads.
     *
     * @param int $id Campaign ID
     * @param int|array $leads Either a single lead ID or an array of lead IDs
     * @param array $tokens Key value array of tokens to send new values for.
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/request-campaign/
     *
     * @return \CSD\Marketo\Response|string
     */
    public function requestCampaign($id, $leads, $tokens = array(), $args = array(), $returnRaw = false)
    {
        $args['id'] = $id;

        $args['input'] = array(
            'leads' => array_map(function ($id) {
                return array('id' => $id);
            }, (array)$leads)
        );

        if (!empty($tokens)) {
            $args['input']['tokens'] = $tokens;
        }

        return $this->getResult('requestCampaign', $args, false, $returnRaw);
    }

    /**
     * Schedule a campaign
     *
     * @param int $id Campaign ID
     * @param \DateTime $runAt The time to run the campaign. If not provided, campaign will be run in 5 minutes.
     * @param array $tokens Key value array of tokens to send new values for.
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/schedule-campaign/
     *
     * @return \CSD\Marketo\Response|string
     */
    public function scheduleCampaign($id, \DateTime $runAt = null, $tokens = array(), $args = array(), $returnRaw = false)
    {
        $args['id'] = $id;

        if (!empty($runAt)) {
            $args['input']['runAt'] = $runAt->format('c');
        }

        if (!empty($tokens)) {
            $args['input']['tokens'] = $tokens;
        }

        return $this->getResult('scheduleCampaign', $args, false, $returnRaw);
    }

    /**
     * Associate a lead
     *
     * @param int $id
     * @param string $cookie
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/associate-lead/
     *
     * @return \CSD\Marketo\Response|string
     */
    public function associateLead($id, $cookie = null, $args = array(), $returnRaw = false)
    {
        $args['id'] = $id;

        if (!empty($cookie)) {
            $args['cookie'] = $cookie;
        }

        return $this->getResult('associateLead', $args, false, $returnRaw);
    }

    /**
     * Get the paging token required for lead activity and changes
     *
     * @param string $sinceDatetime String containing a datetime
     * @param array $args
     * @param bool $returnRaw
     *
     * @return GetPagingToken
     * @link http://developers.marketo.com/documentation/rest/get-paging-token/
     *
     */
    public function getPagingToken($sinceDatetime, $args = array(), $returnRaw = false)
    {
        $args['sinceDatetime'] = $sinceDatetime;

        return $this->getResult('getPagingToken', $args, false, $returnRaw);
    }

    /**
     * Add 1+ custom activities to a lead. Each activity added may be for the same or different lead.
     *
     * @see http://developers.marketo.com/rest-api/lead-database/activities/
     * @see http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#/Activities/addCustomActivityUsingPOST
     *
     * @example: Here's some examples of what the $activities parameter may look like:
     * $activities = [
     *     [ // Example of minimum set of attributes for an activity
     *         'leadId' => 4,
     *         'activityTypeId' => 100002, // Created ahead of time in Marketo Portal Admin
     *         'primaryAttributeValue' => 'FooBar',
     *     ],
     *     [ // Example of all optional attributes used
     *         'leadId' => 6,
     *         'activityTypeId' => 100003, // Created ahead of time in Marketo Portal Admin
     *         'primaryAttributeValue' => 42,
     *         'activityDate' => new \DateTime('+1 day'),
     *         'apiName' => 'FooBar',
     *         'status' => 'updated',
     *         'attributes' => [
     *             [
     *                 'name' => 'quantity',
     *                 'value' => 3,
     *             ],
     *             [
     *                 'name' => 'price',
     *                 'value' => 123.45,
     *                 'apiName' => 'FooBar',
     *             ]
     *         ]
     *     ],
     * ];
     *
     * @param array $activities Array of arrays.
     * @param array $args
     * @param bool $returnRaw
     * @return AddCustomActivitiesResponse
     */
    public function addCustomActivities($activities, $args = array(), $returnRaw = false)
    {
        $args['input'] = [];
        foreach ($activities as $activity) {
            // Validation: Required parameters.
            foreach (['leadId', 'activityTypeId', 'primaryAttributeValue'] as $required) {
                if (!isset($activity[$required])) {
                    throw new \InvalidArgumentException("Required parameter \"{$required}\" is missing.");
                }
            }

            // Validation: Activity date is required by the API, but making it optional here, defaulting to now.
            if (!isset($activity['activityDate'])) {
                $activity['activityDate'] = new \DateTime();
            } elseif (!($activity['activityDate'] instanceof \DateTime)) {
                throw new \InvalidArgumentException('Required parameter "activityDate" must be a DateTime object.');
            }

            // Format required parameters
            $input = [
                'leadId' => (int)$activity['leadId'],
                'activityTypeId' => (int)$activity['activityTypeId'],
                'primaryAttributeValue' => (string)$activity['primaryAttributeValue'],
                'activityDate' => $activity['activityDate']->format('c'),
            ];

            // Optional parameters
            if (isset($activity['apiName'])) {
                $input['apiName'] = (string)$activity['apiName'];
            }
            if (isset($activity['status'])) {
                $input['status'] = (string)$activity['status'];
            }

            // The optional 'attributes' parameter has some validation.
            if (isset($activity['attributes'])) {
                if (!is_array($activity['attributes'])) {
                    throw new \InvalidArgumentException('Optional parameter "attributes" must be an array.');
                }

                $input['attributes'] = []; // Initialize
                foreach ($activity['attributes'] as $attribute) {
                    if (!is_array($attribute)) {
                        throw new \InvalidArgumentException('The "attributes" parameter must contain child array(s).');
                    }
                    // Required child parameters
                    foreach (['name', 'value'] as $required) {
                        if (!isset($attribute[$required])) {
                            throw new \InvalidArgumentException("Required array key \"{$required}\" is missing in the \"attributes\" parameter.");
                        }
                    }
                    $inputAttribute = [
                        'name' => (string)$attribute['name'],
                        'value' => (string)$attribute['value'],
                    ];
                    // Optional child parameters
                    if (isset($attribute['apiName'])) {
                        $inputAttribute['apiName'] = (string)$attribute['apiName'];
                    }

                    $input['attributes'][] = $inputAttribute;
                }
            }

            $args['input'][] = $input;
        }

        return $this->getResult('addCustomActivities', $args, false, $returnRaw);
    }

    /**
     * Get lead changes
     *
     * @param string $nextPageToken Next page token
     * @param string|array $fields
     * @param array $args
     * @param bool $returnRaw
     *
     * @return GetLeadChanges
     * @link http://developers.marketo.com/documentation/rest/get-lead-changes/
     * @see  getPagingToken
     *
     */
    public function getLeadChanges($nextPageToken, $fields, $args = array(), $returnRaw = false)
    {
        $args['nextPageToken'] = $nextPageToken;
        $args['fields'] = (array)$fields;

        if (count($fields)) {
            $args['fields'] = implode(',', $fields);
        }

        return $this->getResult('getLeadChanges', $args, true, $returnRaw);
    }

    /**
     * Update an editable section in an email
     *
     * @param int $emailId
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/asset-api/update-email-content-by-id/
     *
     * @return \CSD\Marketo\Response|string
     */
    public function updateEmailContent($emailId, $args = array(), $returnRaw = false)
    {
        $args['id'] = $emailId;

        return $this->getResult('updateEmailContent', $args, false, $returnRaw);
    }

    /**
     * Update an editable section in an email
     *
     * @param int $emailId
     * @param string $htmlId
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/asset-api/update-email-content-in-editable-section/
     *
     * @return \CSD\Marketo\Response|string
     */
    public function updateEmailContentInEditableSection($emailId, $htmlId, $args = array(), $returnRaw = false)
    {
        $args['id'] = $emailId;
        $args['htmlId'] = $htmlId;

        return $this->getResult('updateEmailContentInEditableSection', $args, false, $returnRaw);
    }

    /**
     * Approve an email
     *
     * @param int $emailId
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/asset-api/approve-email-by-id/
     *
     * @return \CSD\Marketo\Response\ApproveEmailResponse
     */
    public function approveEmail($emailId, $args = array(), $returnRaw = false)
    {
        $args['id'] = $emailId;

        return $this->getResult('approveEmailbyId', $args, false, $returnRaw);
    }

    /**
     * Get lead activities.
     *
     * @param string $nextPageToken
     *   Next page token @see: `::getPagingToken`
     * @param string|array $leads
     * @param string|array $activityTypeIds
     *   Activity Types @see: `::getActivityTypes`.
     * @param array $args
     * @param bool $returnRaw
     *
     * @link http://developers.marketo.com/documentation/rest/get-lead-activities/
     *
     * @return \CSD\Marketo\Response\GetActivitiesResponse|string
     * @see  getPagingToken
     */
    public function getLeadActivity($nextPageToken, $leads, $activityTypeIds, $args = array(), $returnRaw = false)
    {
        $args['nextPageToken'] = $nextPageToken;
        $args['leadIds'] = count((array)$leads) ? implode(',', (array)$leads) : '';
        $args['activityTypeIds'] = count((array)$activityTypeIds) ? implode(',', (array)$activityTypeIds) : '';

        return $this->getResult('getLeadActivity', $args, true, $returnRaw);
    }

    /**
     * Describe the leads object
     *
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Leads/describeUsingGET_2
     *
     * @return Response
     */
    public function describeLeads($returnRaw = false)
    {
        return $this->describeObject('Leads', $returnRaw);
    }

    /**
     * Describe the opportunities object
     *
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Opportunities/describeUsingGET_3
     *
     * @return Response
     */
    public function describeOpportunities($returnRaw = false)
    {
        return $this->describeObject('Opportunities', $returnRaw);
    }

    /**
     * Describe the opportunities roles object.
     *
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Opportunities/describeOpportunityRoleUsingGET
     *
     * @return Response
     */
    public function describeOpportunityRoles($returnRaw = false)
    {
        return $this->describeObject('Opportunities Roles', $returnRaw);
    }

    /**
     * Describe the companies object.
     *
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Companies/describeUsingGET
     *
     * @return Response
     */
    public function describeCompanies($returnRaw = false)
    {
        return $this->describeObject('Companies', $returnRaw);
    }

    /**
     * Describe the Sales Persons object.
     *
     * @param bool|false $returnRaw
     * @throws \Exception
     *
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Sales_Persons/describeUsingGET_4
     *
     * @return Response
     */
    public function describeSalesPersons($returnRaw = false)
    {
        return $this->describeObject('Sales Persons', $returnRaw);
    }

    /**
     * GET Programs
     *
     * @param array $args
     *
     * $args are all optional:
     *  maxReturn (int): max of 200, default = 20
     *  offset (int) : index to begin results at
     *  filterType (string): One of 'id', 'programId', 'folderId', 'workspace'
     *  filterValues (string, comma-separated): Values the filterType should use; e.g., 'Workspace name 1,Workspace 2,...'
     *      if we selected a filterType of 'workspace'
     *  earliestUpdatedAt (ISO-8601 datetime string): Returns programs updated AFTER this date
     *  latestUpdatedAt (ISO-8601 datetime string): Returns programs updated BEFORE this date
     *
     * @param bool $returnRaw
     * @throws \Exception
     * @link http://developers.marketo.com/rest-api/endpoint-reference/asset-endpoint-reference/#!/Programs/browseProgramsUsingGET
     * @return Response
     */
    public function getPrograms($args = array(), $returnRaw = false)
    {

        $defaults = array(
            'maxReturn' => 200,
            'offset' => 0,
            //'filterType' => '',
            //'filterValues' => '',
            //'earliestUpdatedAt' => '',
            //'latestUpdatedAt' => '',
        );

        $args = array_merge($defaults, $args);

        //if we have filterType, filterValues cannot be empty, and vice-versa
        if (
            empty($args['filterType']) && !empty($args['filterValues'])
            ||
            !empty($args['filterType']) && empty($args['filterValues'])
        ) {
            throw new \Exception("Either 'filterType' or 'filterValues' is empty.");
        }

        return $this->getResult('getPrograms', $args, false, $returnRaw);
    }

    /**
     * GET Channels
     *
     * @param array $args
     *
     * $args are all optional:
     *  maxReturn (int): max of 200, default = 20
     *  offset (int) : index to begin results at
     *
     * @param bool $returnRaw
     * @link http://developers.marketo.com/rest-api/endpoint-reference/asset-endpoint-reference/#!/Programs/browseProgramsUsingGET
     * @return Response
     */
    public function getChannels($args = array(), $returnRaw = false)
    {
        $defaults = array(
            'maxReturn' => 200,
            'offset' => 0,
        );

        $args = array_merge($defaults, $args);

        return $this->getResult('getChannels', $args, false, $returnRaw);
    }

    /**
     * POST leads/push.json
     *
     * Push leads to marketo (allows us to push directly into a program)
     *
     * @param array $leads An array of leads (each lead is an associative array itself)
     * @param array $args
     *
     * $args (all optional):
     *  lookupField (string) Defaults to 'email'
     *  partitionName (string) Name of Partition to add lead to
     *  programName (string) Name of the Program to add lead to
     *  programStatus (string) Name of the progression status in the program the new leads should have
     *                         (must be a valid progression status from this Program's associated Channel)
     *  reason (string)
     *  source (string)
     *
     * @param bool $returnRaw
     *
     * @throws \Exception
     * @link http://developers.marketo.com/rest-api/endpoint-reference/lead-database-endpoint-reference/#!/Leads/pushToMarketoUsingPOST
     * @return Response
     *
     */
    public function pushLeads($leads, $args = array(), $returnRaw = false)
    {

        if (!is_array($leads)) {
            $leads = (array)$leads;
        }

        if (empty($leads)) {
            throw new \Exception('Leads cannot be empty.');
        }

        $defaults = array(
            'input' => $leads,
            'lookupField' => 'email',
            //'partitionName' => '',
            //'programName' => '',
            //'programStatus' => '',
            //'reason' => '',
            //'source' => '',
        );

        $args = array_merge($defaults, $args);

        return $this->getResult('pushLeads', $args, false, $returnRaw);
    }

    /**
     * POST leads/programs/{programId}/status.json
     *
     * Updates a user's program membership
     *
     * @param array $leads
     * @param int $program_id
     * @param string $status The leads' collective membership status for the given program id
     * @param array $args
     * @param bool $returnRaw
     *
     */
    public function updateProgramStatus($leads = array(), $program_id = 0, $status = '', $args = array(), $returnRaw = false)
    {
        if (!is_array($leads)) {
            $leads = (array)$leads;
        }

        if (empty($leads)) {
            throw new \Exception('Leads cannot be empty.');
        }

        if (empty($program_id)) {
            throw new \Exception('Program id cannot be empty.');
        }

        if (empty($status)) {
            throw new \Exception('Please provide a program status.');
        }

        $defaults = array(
            'input' => $leads,
            'programId' => $program_id,
            'lookupField' => 'id',
            'status' => $status,
        );

        $args = array_merge($defaults, $args);

        return $this->getResult('updateProgramStatus', $args, false, $returnRaw);
    }

    /**
     * Generic method to describe a Marketo object.
     *
     * @param string $objectName
     * @param bool|false $returnRaw
     * @return Response
     * @throws \Exception
     */
    private function describeObject($objectName, $returnRaw = false)
    {
        if (!isset($this->marketoObjects[$objectName])) {
            throw new \Exception('describeObject() Expected parameter $objectName, to be a valid Marketo object ' . "but $objectName provided");
        };

        $args['objectName'] = $this->marketoObjects[$objectName];
        return $this->getResult('describeObject', $args, false, $returnRaw);
    }

    /**
     * Internal helper method to actually perform command.
     *
     * @param string $command
     * @param array $args
     * @param bool $fixArgs
     * @param bool $returnRaw
     *
     * @return \CSD\Marketo\Response|string
     */
    private function getResult($command, $args, $fixArgs = false, $returnRaw = false)
    {
        $cmd = $this->getCommand($command, $args);

        // Marketo expects parameter arrays in the format id=1&id=2, Guzzle formats them as id[0]=1&id[1]=2.
        // Use a quick regex to fix it where necessary.
        if ($fixArgs) {
            $cmd->prepare();

            $url = preg_replace('/id%5B([0-9]+)%5D/', 'id', $cmd->getRequest()->getUrl());
            $cmd->getRequest()->setUrl($url);
        }

        $cmd->prepare();

        if ($returnRaw) {
            return $cmd->getResponse()->getBody(true);
        }

        return $cmd->getResult();
    }
}
