<?php

namespace  Matcha\Controllers\Search;

use Matcha\Controllers\Controller;
use Slim\Http\Request;
use Slim\Http\Response;
use Matcha\Models\User;
use Matcha\Models\Likes;
use Matcha\Models\About;
use Respect\Validation\Validator as v;
use Matcha\Controllers\Search\MatchaController;

class SearchController extends Controller
{
    public function getAllProfile($request, $response)
    {
        // узнать кто нравится
        $user = User::where('id', $_SESSION['user'])->first();
        $about = About::where('user_id', $user->id)->first();

        //  желанный результат
        $prefer = $about->sexual_pref;

        $allPrefer = About::where('sexual_pref', $prefer)->get();

        // поиск и запись всех кто нужен по полу
        foreach ($allPrefer as $row) {
            $arr[] = $row->user_id;
        }

        // всех bi туда же
        $allPreferBi = About::where('sexual_pref', "bi")->get();

        foreach ($allPreferBi as $row) {
            $arr[] = $row->user_id;
        }

        // все юзеры по базе User
        foreach ($arr as $user_id) {
            $searchUser[] = User::where('id', $user_id)->first();
        }
        // все юзеры в глобальном окружении
        $this->container->view->getEnvironment()->addGlobal('allUserForSearch', $searchUser);

        // все юзеры по базе About
        foreach ($arr as $user_id) {
            $aboutUser[] = About::where('user_id', $user_id)->first();
        }
        // все о юзерах в глобальном окружении
        $this->container->view->getEnvironment()->addGlobal('allUserForSearch', $aboutUser);

        return $this->view->render($response, 'search/all.twig');
    }
}