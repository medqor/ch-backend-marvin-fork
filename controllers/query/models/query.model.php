<?php

trait model
{
    use sql;


    public function get_provider_taxonomies($npis)
    {
        $sql = sprintf('SELECT hp.npi, codes.code, codes.classification, codes.specialization FROM nppes_healthcare_provider hp join nppes_taxonomy_codes codes on codes.code = hp.healthcare_provider_taxonomy_code where hp.npi in(%s) ', implode(",", array_keys($npis)));
        $data = cached($this->db, 'provider_taxonomies', $sql, [], true);

        foreach ($data as $row) {
            $npi = $row['npi'];
            unset($row['npi']);
            $npis[$npi]['taxonomy'][$row['code']] = $row;
        }
        foreach ($npis as $idx => $row) {
            $npis[$idx]['taxonomy'] = array_values($npis[$idx]['taxonomy']);
        }
        pretty_print_r($npis);
        return array_values($npis);


    }

    public function organization_name($val)
    {
        if (!trim($val)) {
            return;
        }


        $this->search[] = '(  provider_organization_name  ilike :organization_name  OR provider_other_organization_name  ilike :organization_name)';
        $this->sqlparams[':organization_name'] = '%' . $val . '%';
    }


    public function first_name($val)
    {
        if (!trim($val)) {
            return;
        }
        #$this->search[] = '( nppes_data.authorized_official_first_name  ilike :first_name OR nppes_data.provider_first_name  ilike :first_name OR nppes_data.provider_other_first_name  ilike :first_name)';
        $this->search[] = '(  provider_first_name  ilike :first_name )';
        $this->sqlparams[':first_name'] = str_replace("*", "%", $val);
        if(substr($this->sqlparams[':first_name'],-1)!='%'){
            $this->sqlparams[':first_name'].='%';
        }
    }

    public function last_name($val)
    {
        if (!trim($val)) {
            return;
        }
        #$this->search[] = ' (nppes_data.authorized_official_last_name  ilike :last_name OR nppes_data.provider_last_name  ilike :last_name OR nppes_data.provider_other_last_name  ilike :last_name)';
        $this->search[] = ' ( provider_last_name  ilike :last_name)';
        $this->sqlparams[':last_name'] = str_replace("*", "%", $val);
    }


    public function city($val)
    {
        $addressType = $this->getParam('addressType', false);
        switch ($addressType) {
            case 'physical':
            case 'practice':
                $this->search[] = ' ( p.provider_business_practice_location_address_city_name  ilike :city OR c.city ilike:city OR h.city ilike :city )';
                break;
            case 'mailing':
                $this->search[] = ' (p.provider_business_mailing_address_city_name  ilike :city OR c.city ilike:city OR h.city ilike :city)';
                break;
            default:
                $this->search[] = ' (p.provider_business_mailing_address_city_name  ilike :city OR p.provider_business_practice_location_address_city_name  ilike :city  OR c.city ilike:city OR h.city ilike :city)';
                break;
        }


        $this->sqlparams[':city'] = $val;
    }

    public function state($val)
    {
        $addressType = $this->getParam('addressType',  false);
        switch ($addressType) {
            case 'physical':
            case 'practice':
                $this->search[] = ' ( p.provider_business_practice_location_address_state_name  ilike :state )';
                break;
            case 'mailing':
                $this->search[] = ' (p.provider_business_mailing_address_state_name  ilike :state )';
                break;
            default:
                $this->search[] = ' (p.provider_business_mailing_address_state_name  ilike :state OR p.provider_business_practice_location_address_state_name  ilike :state )';
                break;
        }
        $this->sqlparams[':state'] = $val;
    }

    public function zip_code($val)
    {
        $addressType = $this->getParam('addressType',false);
        switch ($addressType) {
            case 'physical':
            case 'practice':
                $this->search[] = ' ( p.provider_business_practice_location_address_postal_code  ilike :zip_code  )';
                break;
            case 'mailing':
                $this->search[] = ' (p.provider_business_mailing_address_postal_code  ilike :zip_code)';
                break;
            default:
                $this->search[] = ' (p.provider_business_mailing_address_postal_code  ilike :zip_code OR p.provider_business_practice_location_address_postal_code  ilike :zip_code )';
                break;
        }

        $this->sqlparams[':zip_code'] = $val;
    }


//    public function npi($val)
//    {
//        $this->search[] = ' (p.npi = :npi)';
//        $this->sqlparams[':npi'] = $val;
//    }

    public function npi($val)
    {

        if (!trim($val)) {
            return;
        }
        if (!strstr($val, ",")) {
            $this->search[] = "(p.npi = :npi )";
            $val = trim($val);
            $this->fetchSingleRecord = true;
        } else {
            $this->search[] = "(  POSITION ('|' || p.npi || '|' IN :npi ))";
            $val = explode(",", $val);
            $val = array_map("trim", $val);
            $val = '|' . implode("|", $val) . '|';

        }
        #$this->search[] = '( nppes_data.authorized_official_first_name  ilike :first_name OR nppes_data.provider_first_name  ilike :first_name OR nppes_data.provider_other_first_name  ilike :first_name)';

        $this->sqlparams[':npi'] = $val;
    }

    public function type($val)
    {
        $this->search[] = "  (p.entity_type_code=:type)";
        $this->sqlparams[':type'] = $val;


    }

    public function taxonomy($val)
    {

        $this->join[] = " LEFT JOIN nppes_healthcare_provider as hp ON hp.npi = p.npi ";
        if (strstr($val, ',')) {
            $this->search[] = ' POSITION(hp.healthcare_provider_taxonomy_code  IN :taxonomy)>0';
            $this->sqlparams[':taxonomy'] = $val;
        } else {
            $this->search[] = ' (hp.healthcare_provider_taxonomy_code =:taxonomy)';
            $this->sqlparams[':taxonomy'] = $val;
        }


    }

    protected function person(){

        $this->data['limit'] = $this->getParam('limit',  5000);
        $this->data['offset'] = $this->getParam('offset',  0);
        $this->data['feature'] = $this->getParam('feature',false);
        $this->data['summary'] = $this->getParam('summary',  false);
        $this->data['subset'] = $this->getParam('subset',  false);
        $this->sqlparams=[];
        $this->memberType = " AND p.entity_type_code=1 ";
        $fields = ['type', 'organization_name', 'first_name', 'last_name', 'city', 'state', 'zip_code', 'taxonomy', 'npi'];
        $this->search = [];
        $this->search[] ="  p.entity_type_code=1 ";
        $this->join = [];

        foreach ($fields as $field) {
            $exists = $this->_variables[$field]??false;
            if ($exists && trim($exists)) {

                $this->data[$field] = $exists;
                $this->$field($exists);
            }
        }

        $sql = sprintf(" SELECT 
            p.npi,
            
            p.provider_first_name as first_name,
            p.provider_middle_name as middle_name, 
            p.provider_last_name as last_name,
            p.provider_name_suffix_text as suffix,
            p.provider_credential_text as creds,
        
            
            p.provider_first_line_business_practice_location_address as practiceAddress,
            p.provider_second_line_business_practice_location_address as practiceAddress2,
            p.provider_business_practice_location_address_city_name as practiceCity,
            p.provider_business_practice_location_address_state_name as practiceState,
            p.provider_business_practice_location_address_postal_code as practicePostalCode,
            p.provider_business_practice_location_address_country_code as practiceCountryCode
 

        
            
            FROM nppes_data as p
           
            %s
            WHERE
                  %s 
            
            
            ",
            implode(" ", $this->join),
            implode(" AND ", $this->search));

            $sql .= " ORDER BY 6,4,5,2  offset :offset limit :limit";
            $params = $this->sqlparams;
$this->data['limit']=10000;
$this->data['offset']=isset($_SESSION['offset'])?$_SESSION['offset']:302000;

                $params[':offset'] = $this->data['offset'];
                $params[':limit']=$this->data['limit'] ;
                $data = $this->query('_redshift', $sql, $params, false, ['collection' => 'people', 'variables' => $this->_variables]);
                $this->data['offset']+=$this->data['limit'];
                $_SESSION['offset']= $this->data['offset'];

          $this->_result=['count'=>count($data),'offset'=>$_SESSION['offset']];

//        if ($this->data['offset'] != false) {
//            $sql.=" OFFSET :offset ";
//            $params[':offset']=$this->data['offset'] ;
//        }
//        if ($this->data['limit'] != false) {
//            $sql.=" LIMIT :limit ";
//            $params[':limit']=$this->data['limit'] ;
//        }







    }


}
