@extends('layouts.app')
@section('content')

    <div class="navbar navbar-light customPanel categorList documents-manager-shell">
        @if( count( $filters ) > 0)
            <div class="documents-manager-grid">
                @foreach($filters AS $filter)
                    <div class="documents-manager-card">
                        <div class="documents-manager-card__header"> {{$filter['category']}} </div>
                        <div>
                            @if( ( count($filter['elements']) > 0 ) && ( $filter['category'] != 'manifest' ) )
                                <select class="selectedElement documents-manager-card__control" name="selectedElement">
                                    <option value="" style="text-align: center;">Select an option</option>
                                    @foreach($filter['elements'] AS $element)
                                        <option style="text-align: center;" value="{{route('documentsManager.listDocuments', ['category' => $filter['category'], 'element' => $element])}}">{{str_replace('.', ' > ', $element)}}</option>
                                    @endforeach
                                </select>    
                            @else
                            <div class="documents-manager-card__link">
                                <a class="aLink" href="{{route('documentsManager.listDocuments', ['category' => $filter['category'], 'element' => 'others'])}}" style="text-decoration: none;">Check documents</a>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div class="documents-manager-card">
                    <div class="documents-manager-card__header"> SEARCH </div>
                    <div class="documents-manager-search">
                        <input type="text" name="search_document" id="search_document" onkeypress="handle(event)">
                        <button onclick="searchDocuments()" class="btn btn-primary"> <i class="fa-solid fa-magnifying-glass"></i> </button>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning" role="alert" style="padding: 10px; margin-bottom: 0;"> NO FILES AVAILABLE</div>
        @endif
    </div>

    <div class="searchList"> </div>
    
    @include("customTools.documentsManager.includes.js")
    
    <style>
        a.aLink{ color: #000; }
        a.aLink:hover{ color: dodgerblue; }
        .documents-manager-shell { display: block; }
        .documents-manager-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
            width: 100%;
        }
        .documents-manager-card {
            border-radius: 5px;
            min-width: 0;
        }
        .documents-manager-card__header {
            background-color: dodgerblue;
            border: 1px solid #999;
            border-radius: 5px 5px 0 0;
            color: #fff;
            padding: 10px;
            text-align: center;
            text-transform: uppercase;
        }
        .documents-manager-card__control,
        .documents-manager-card__link {
            background-color: #fff;
            border: 1px solid #999;
            border-radius: 0 0 5px 5px;
            min-height: 43px;
            text-align: center;
            width: 100%;
        }
        .documents-manager-card__control { padding: 10px; }
        .documents-manager-card__link { padding: 10px; }
        .documents-manager-search {
            align-items: stretch;
            background-color: #fff;
            border: 1px solid #999;
            border-radius: 0 0 5px 5px;
            display: flex;
        }
        .documents-manager-search input {
            border: 0;
            border-radius: 0 0 0 5px;
            flex: 1;
            font-size: 18px;
            height: 42px;
            min-width: 0;
            padding: 2px 8px;
        }
        .documents-manager-search .btn {
            border-radius: 0 0 5px 0;
            min-width: 44px;
        }
    </style>
@endsection
