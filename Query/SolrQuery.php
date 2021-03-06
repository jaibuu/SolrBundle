<?php
namespace FS\SolrBundle\Query;

class SolrQuery extends AbstractQuery
{

    /**
     * @var array
     */
    private $mappedFields = array();

    /**
     * @var array
     */
    private $searchTerms = array();

    /**
     * @var array
     */
    private $searchOperators = array();

    /**
     * @var bool
     */
    private $useAndOperator = false;

    /**
     * @var bool
     */
    private $useWildcards = true;

    /**
     * @var string
     */
    private $customQuery = '';

    /**
     * @var string
     */
    private $partialQuery = '';

    /**
     * @var array
     */
    private $response = array();

    /**
     * @var object
     */
    private $clientQuery;

    /**
     * @return array
     */
    public function createSelect()
    {
        return $this->solr->createSelectQuery($this);
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->solr->query($this, true);
    }

    /**
     * @return array
     */
    public function getSolrResult()
    {
        return $this->solr->query($this, false);
    }
    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getMappedFields()
    {
        return $this->mappedFields;
    }

    /**
     * @param array $mappedFields
     */
    public function setMappedFields($mappedFields)
    {
        $this->mappedFields = $mappedFields;
    }

    /**
     * @param bool $strict
     */
    public function setUseAndOperator($strict)
    {
        $this->useAndOperator = $strict;
    }

    /**
     * @param bool $boolean
     */
    public function setUseWildcard($boolean)
    {
        $this->useWildcards = $boolean;
    }

    /**
     * @return string
     */
    public function getCustomQuery()
    {
        return $this->customQuery;
    }

    /**
     * @param string $query
     */
    public function setCustomQuery($query)
    {
        $this->customQuery = $query;
    }

    /**
     * @return string
     */
    public function getPartialQuery()
    {
        return $this->partialQuery;
    }

    /**
     * @param string $query
     */
    public function setPartialQuery($query)
    {
        $this->partialQuery = $query;
    }

    /**
     * Enclosing query in parentheses
     */
    public function encloseQuery( $append = '(', $prepend =  ')' )
    {
        $this->partialQuery = $append . $this->getQuery() . $prepend;
        $this->clearSearchTerms();
    }

    /**
     * @param array $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function clearSearchTerms()
    {
        $this->searchTerms = array();
    }

    /**
     * @return array
     */
    public function getSearchTerms()
    {
        return $this->searchTerms;
    }

    /**
     * @return array
     */
    public function getSearchOperators()
    {
        return $this->searchOperators;
    }

    /**
     * @param array $value
     */
    public function queryAllFields($value)
    {
        $this->setUseAndOperator(false);

        foreach ($this->mappedFields as $documentField => $entityField) {
            $this->searchTerms[$documentField] = $value;
        }
    }

    /**
     *
     * @param string $field
     * @param string $value
     * @return SolrQuery
     */
    public function addSearchTerm($field, $value, $operator = null)
    {
        $documentFieldsAsValues = array_flip($this->mappedFields);

        if (array_key_exists($field, $documentFieldsAsValues)) {
            $documentFieldName = $documentFieldsAsValues[$field];

            $this->searchTerms[$documentFieldName] = $value;

            //adding operator before the current field
            end($this->searchOperators);
            $this->searchOperators[key($this->searchOperators)] = $this->searchOperators[$documentFieldName] = $operator;
            reset($this->searchOperators);
        }

        return $this;
    }

    /**
     * @param string $field
     * @return SolrQuery
     */
    public function addField($field)
    {
        $entityFieldNames = array_flip($this->mappedFields);
        if (array_key_exists($field, $entityFieldNames)) {
            parent::addField($entityFieldNames[$field]);
        }

        return $this;
    }

    public function getClientQuery(){
        return $this->clientQuery;        
    }

    public function setClientQuery($clientQuery){
        $this->clientQuery = $clientQuery;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        if ($this->customQuery) {
            $this->setQuery($this->customQuery);
            return $this->customQuery;
        }

        $term = '';

        if ($this->partialQuery) {
            $term = $this->partialQuery;
        }


        if (count($this->searchTerms) == 0) {
            return $term;
        }

        $logicOperator = 'AND';
        if (!$this->useAndOperator) {
            $logicOperator = 'OR';
        }

        $termCount = 1;

        foreach ($this->searchTerms as $fieldName => $fieldValue) {

            if ($this->useWildcards) {
                $term .= $fieldName . ':*' . $fieldValue . '*';
            } else {
                $term .= $fieldName . ':' . $fieldValue;
            }

            if ($termCount < count($this->searchTerms)) {
                if($this->searchOperators[$fieldName]){
                    $term .= ' ' . $this->searchOperators[$fieldName] . ' ';
                } else {
                    $term .= ' ' . $logicOperator . ' ';
                }
            }

            $termCount++;
        }

        $this->setQuery($term);

        return $term;
    }

}
