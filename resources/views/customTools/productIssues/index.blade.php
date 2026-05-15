@extends('layouts.app')
@section('content')

    @include("customTools.productIssues.includes.add")
    @include("customTools.productIssues.includes.js")

    <style>
        
        #issuesTable td:nth-child(5),  /* ISSUE */
        #issuesTable td:nth-child(6),  /* ISSUE */
        #issuesTable td:nth-child(7) { /* CONCLUSION */
            max-width: 200px;       /* ajusta conforme precisa */
            white-space: normal !important; 
            word-wrap: break-word;  
            word-break: break-word; 
        }
        
        
    </style>
    <div class="navbar navbar-light customPanel categorList">
        <table id="issuesTable" class="table table-striped display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>ID ORDER</th>
                    <th>MANUFACTURER</th>
                    <th>REFERENCE</th>
                    <th>CAR</th>
                    <th>ISSUE</th>
                    <th>CONCLUSION</th>
                    <th>STATUS</th>
                    <th>DOCUMENTS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($productIssues AS $issue)
                    <tr>
                        <td>{{$issue->date}}</td>
                        <td>{{$issue->id_order}}</td>
                        <td>{!!$issue->manufacturer!!}</td>
                        <td>{{$issue->reference}}</td>
                        <td>{{$issue->car}}</td>
                        <td>{{$issue->description}}</td>
                        <td>{{$issue->conclusion}}</td>
                        <td>
                            @if($issue->status == 'NO SOLUTION') <i class="fa-solid fa-xmark" style="color: red;"></i><span style="color: transparent; width: 5px;display: none;">PENDING</span>
                            @elseif($issue->status == 'SOLVED')  <i class="fa-solid fa-check" style="color: green;"></i><span style="color: transparent; width: 5px;display: none;">SOLVED</span>
                            @elseif($issue->status == 'PENDING') <i class="fa-solid fa-xmark" style="color: red;"></i><span style="color: transparent; width: 5px;display: none;">PENDING</span>
                            @endif
                        </td>
                        <td>
                            @if(count($issue->files) > 0)
                                @foreach($issue->files AS $file)
                                    <a href="{{ config('allstars.services.webtools.base_url') }}/uploads/{{$file}}" download="{{$issue->reference}}">
                                        <i class="fa-solid fa-image" style="font-size: 22px; padding: 7px; color: dodgerblue;"></i>
                                    </a>
                                @endforeach
                            @else
                                <span style="color: red;">NO IMAGES</span>
                            @endif
                        </td>
                        <td> 
                            <a style="padding: 1px 6px; color: orange;" href="{{ route('productIssues.edit', [ 'id' => $issue->id_issue ]) }}">
                                <i class="fa-solid fa-pencil" style="font-size: 18px;"></i>
                            </a> 
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
