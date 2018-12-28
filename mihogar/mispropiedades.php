<?php
class MihogarApiResourceMispropiedades extends ApiResource
{
    public function get()
    {
        $user = JFactory::getUser();
        $userid = $user->get('id');
        $retornar = new JObject;
        $retornar->id = $user->get('id');
        $retornar->name = $user->get('name');
        $retornar->username = $user->get('username');
        $retornar->email = $user->get('email');

        if($user->get('id')>0){
            $retornar = $this->getMyProperties($user->get('id'));
        }else{
            $retornar = 'inmobiliaria inexistente';
        }

        $this->plugin->setResponse( $retornar );

    }

    private function getMyProperties($userId){
            $agent_id = $userId;
            
            $db = JFactory::getDBO();
            $query = "SELECT p.id, p.ref, p.pro_name, p.pro_alias, p.agent_id, p.company_id, p.price";
            $query .= ", p.pro_small_desc, p.pro_full_desc, p.pro_type, p.isFeatured, p.lat_add, p.long_add, pro_video ";
            $query .= ", p.city, p.state, p.address, p.bed_room, p.bath_room, p.rooms ";
            $query .= ", p.parking, p.square_feet, p.lot_size, p.curr, p.published ";
            $query .= ", a.name as agent_name, a.user_id, a.photo, a.published as agent_published, a.mobile";
            $query .= ", ct.city as city_name ";
            $query .= ", t.type_name ";
            $query .= ", c.category_name ";
            $query .= ", pc.category_id ";
            $query .= ", i.image, i.id as image_id, i.ordering as iordering ";
            $query .= " FROM #__osrs_properties as p ";
            $query .= " INNER JOIN #__osrs_agents as a on a.id = p.agent_id";
            $query .= " LEFT JOIN #__osrs_cities as ct on ct.id = p.city";
            $query .= " LEFT JOIN #__osrs_types as t on t.id = p.pro_type";
            $query .= " LEFT JOIN #__osrs_property_categories as pc on pc.pid = p.id";            
            $query .= " LEFT JOIN #__osrs_categories as c on c.id = pc.category_id";
            $query .= " INNER JOIN #__osrs_photos as i on i.pro_id = p.id and (i.ordering = 1)";
            $query .= " WHERE p.published >= 0 ";
    
            if($agent_id>0){
                $query .= " AND a.user_id = $agent_id ";
            }
    
            $query .= " GROUP BY p.id " ;
            $query .= " ORDER BY p.published DESC, p.id DESC " ;
            $query .= "LIMIT 0, 100 ";
    
            //echo str_replace('#_','luxy',$query);
            
            $db->setQuery($query);
            $properties = $db->loadObjectList();
            
            for ($i=0; $i < count($properties); $i++) { 
                if($properties[$i]->pro_small_desc == ''){
                    $pro_small_desc = strip_tags($properties[$i]->pro_full_desc);
                    $properties[$i]->pro_small_desc = substr($pro_small_desc, 0,300) . ' ...';
                }
                    
            $categoriesArray = [];
            $query2 = " SELECT c.id FROM #__osrs_property_categories as pc ";                     
            $query2 .= " LEFT JOIN #__osrs_categories as c on c.id = pc.category_id";
            $query2 .= " WHERE pc.pid = " . $properties[$i]->id;  
            $db->setQuery($query2);
            $categories = $db->loadObjectList();
            /*foreach($categories as $c){
                $categoriesArray[] = $c->id;
            }*/
            $properties[$i]->categoryIds = $categories;
            //print_r($properties[$i]->categoryIds); 
                //}            
            }           
            
            // Output the JSON data.
            //echo count($properties);
            echo json_encode($properties);    
            exit;
            // currency 1 $, 54 u$s
       

    }
}
?>