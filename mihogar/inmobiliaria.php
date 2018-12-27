<?php
class MihogarApiResourceInmobiliaria extends ApiResource
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

        $this->plugin->setResponse( $retornar );
    }
}
?>