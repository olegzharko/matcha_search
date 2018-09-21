<?php

namespace  Matcha\Controllers\Search;

use Matcha\Controllers\Controller;
use Slim\Http\Request;
use Slim\Http\Response;
use Matcha\Models\User;
use Matcha\Models\Liked;
use Matcha\Models\About;

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

    public function getLike($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'like' => v::notEmpty(),
            'liked_id' => v::notEmpty(),
        ]);

        if ($validation->failed()) {
            $this->flash->addMessage('info', 'Fail');
            return $response->withRedirect($this->router->pathFor('search.all'));
        }

        Liked::create([
            'user_id' => $_SESSION['user'],
            'liked_id' => $request->getParam('liked_id'),
        ]);

        $user_id = $request->getParam('liked_id');

        $likedUser = User::where('id', $user_id)->first();

        $newRating = $likedUser->rating + 1;

        // нужна новая графа в базе для рейтинка
        User::where('id', $user_id)->update([
            'rating' => $newRating,
        ]);

        return $response->withRedirect($this->router->pathFor('search.all'));
    }

//    public function getUnlike($request, $response)
//    {
//        $validation = $this->validator->validate($request, [
//            'unlike' => v::notEmpty(),
//            'liked_id' => v::notEmpty(),
//        ]);
//
//        if ($validation->failed()) {
//            $this->flash->addMessage('info', 'Fail');
//            return $response->withRedirect($this->router->pathFor('search.all'));
//        }
//
//        Liked::where([
//            'user_id' => $_SESSION['user'],
//            'liked_id' => $request->getParam('liked_id'),
//        ])->delete();
//
//        $user_id = $request->getParam('liked_id');
//
//        $likedUser = User::where('id', $user_id)->first();
//
//        $newRating = $likedUser->rating - 1;
//
//        // нужна новая графа в базе для рейтинка
//        if ($newRating >= 0)
//        {
//            User::where('id', $user_id)->update([
//                'rating' => $newRating,
//            ]);
//        }
//
//        return $response->withRedirect($this->router->pathFor('search.all'));
//    }
}