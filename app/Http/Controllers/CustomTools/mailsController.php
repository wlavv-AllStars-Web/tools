<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

class mailsController extends Controller
{
    public function __construct(){ }

    public function createStructure($origin, $template, $subject, $data, $id_lang)
    {
        $current_locale = App::getLocale();

        App::setLocale( $this->getLocaleFromIDLang($id_lang) );

        $html = '';
        
        if($origin == 'none'){
            $html .= $this->getContent($origin, $template, $data);
        }else{
            $html .= $this->getHeader($origin, $subject);
            $html .= $this->getCSS($origin);
            $html .= $this->getContent($origin, $template, $data);
            $html .= $this->getFooter($origin);
        }

        App::setLocale($current_locale);
        
        return $html;
    }
    
    public function getHeader($origin, $subject){

        $data = [ 'subject' => $subject ];
        return view('mails.includes.header.' . $origin, compact('data'))->render();
    }
    
    public function getCSS($origin){
        
        $data = [];
        return view('mails.includes.css.' . $origin, compact('data'))->render();
    }
    
    public function getContent($origin, $template, $data){

        return view('mails.' . $template, compact('data'))->render();
    }
    
    public function getFooter($origin){
        
        $data = [];
        return view('mails.includes.footer.' . $origin, compact('data'))->render();
    }
    
    public function send($email, $html, $subject){

        Mail::html($html, function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });
    }
    
    public function getLocaleFromIDLang($id_lang){

        switch ($id_lang) {
            case 1:
                $locale = 'en';
                break;
            case 4:
                $locale = 'es';
                break;
            case 5:
                $locale = 'fr';
                break;
            default:
                $locale = 'en';
        }

        return $locale;
    }
}
