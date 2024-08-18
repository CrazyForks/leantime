@extends($layout)
@section('content')

@php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$allCanvas = $allCanvas;
$canvasTitle = "";
$canvasLabels = $canvasLabels;

//get canvas title
foreach ($allCanvas as $canvasRow) {
    if ($canvasRow["id"] == $currentCanvas) {
        $canvasTitle = $canvasRow["title"];
        break;
    }
}
@endphp

<div class="pageheader">
    <div class="pageicon"><i class="far fa-lightbulb"></i></div>
    <div class="pagetitle">
        <h5>{{ session("currentProjectClient") ?? '' . " // " . session("currentProjectName") }}</h5>
        @if (count($allCanvas) > 0)
        <span class="dropdown dropdownWrapper headerEditDropdown">
            <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
            <ul class="dropdown-menu editCanvasDropdown ">
                @if ($login::userIsAtLeast($roles::$editor))
                <li><a href="#/ideas/boardDialog/{{ $currentCanvas }}">{!! __("links.icon.edit") !!}</a></li>
                <li><a href="{{ BASE_URL }}/ideas/delCanvas/{{ $currentCanvas }}" class="delete">{!! __("links.icon.delete") !!}</a></li>
                @endif
            </ul>
        </span>
        @endif
        <h1>{!! __("headlines.ideas") !!}
            //
            @if (count($allCanvas) > 0)
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    {{ $canvasTitle }}&nbsp;<i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu canvasSelector">
                    @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/ideas/boardDialog">{!! __("links.icon.create_new_board") !!}</a></li>
                    @endif
                    <li class="border"></li>
                    @foreach ($allCanvas as $canvasRow)
                    <li><a href='{{ BASE_URL }}/ideas/showBoards/{{ $canvasRow["id"] }}'>{{ $canvasRow["title"] }}</a></li>
                    @endforeach
                </ul>
            </span>
            @endif
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner" id="ideaBoards">

        <div class="row">
            <div class="col-md-4">
                @if ($login::userIsAtLeast($roles::$editor))
                @if (count($allCanvas) > 0)
                <a href="#/ideas/ideaDialog?type=idea" class="btn btn-primary" id="customersegment">
                    <span class="far fa-lightbulb"></span>{!! __("buttons.add_idea") !!}
                </a>
                @endif
                @endif
            </div>

            <div class="col-md-4 center">
            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <button class="btn dropdown-toggle" data-toggle="dropdown">{!! __("buttons.idea_wall") !!} {!! __("links.view") !!}</button>
                        <ul class="dropdown-menu">
                            <li><a href="{{ BASE_URL }}/ideas/showBoards" class="active">{!! __("buttons.idea_wall") !!}</a></li>
                            <li><a href="{{ BASE_URL }}/ideas/advancedBoards" class="">{!! __("buttons.idea_kanban") !!}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>
        @if (count($allCanvas) > 0)
        <div id="ideaMason" class="sortableTicketList" style="padding-top:10px;">
            @foreach ($canvasItems as $row)
            <div class="ticketBox" id="item_{{ $row['id'] }}" data-value="{{ $row['id'] }}">
                <div class="row">
                    <div class="col-md-12">
                        @if ($login::userIsAtLeast($roles::$editor))
                        <div class="inlineDropDownContainer" style="float:right;">
                            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <ul class="dropdown-menu">
                                <li class="nav-header">{!! __("subtitles.edit") !!}</li>
                                <li><a href="#/ideas/ideaDialog/{{ $row['id'] }}" class="" data="item_{{ $row['id'] }}">{!! __("links.edit_canvas_item") !!}</a></li>
                                <li><a href="#/ideas/delCanvasItem/{{ $row['id'] }}" class="delete" data="item_{{ $row['id'] }}">{!! __("links.delete_canvas_item") !!}</a></li>
                            </ul>
                        </div>
                        @endif

                        <h4><a href="#/ideas/ideaDialog/{{ $row['id'] }}" data="item_{{ $row['id'] }}">{{ $row['description'] }}</a></h4>

                        <div class="mainIdeaContent">
                            <div class="kanbanCardContent">
                                <div class="kanbanContent" style="margin-bottom: 20px">
                                    {!! $row['data'] !!}
                                </div>
                            </div>
                        </div>

                        <div class="clearfix" style="padding-bottom: 8px;"></div>

                        <div class="dropdown ticketDropdown statusDropdown show firstDropdown colorized">
                            <a class="dropdown-toggle f-left status {{ $canvasLabels[$row['box']]['class'] }}" href="javascript:void(0);" role="button" id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">{{ $canvasLabels[$row['box']]['name'] }}</span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{!! __("dropdown.choose_status") !!}</li>
                                @foreach ($canvasLabels as $key => $label)
                                <li class='dropdown-item'>
                                    <a href='javascript:void(0);' class='{{ $label['class'] }}' data-label='{{ $label['name'] }}' data-value='{{ $row['id'] }}_{{ $key }}_{{ $label['class'] }}' id='ticketStatusChange{{ $row['id'] }}{{ $key }}'>{{ $label['name'] }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                            <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">
                                    @if ($row["authorFirstname"] != "")
                                    <span id='userImage{{ $row['id'] }}'><img src='{{ BASE_URL }}/api/users?profileImage={{ $row['author'] }}' width='25' style='vertical-align: middle;' /></span><span id='user{{ $row['id'] }}'></span>
                                    @else
                                    <span id='userImage{{ $row['id'] }}'><img src='{{ BASE_URL }}/api/users?profileImage=false' width='25' style='vertical-align: middle;' /></span><span id='user{{ $row['id'] }}'></span>
                                    @endif
                                </span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{!! __("dropdown.choose_user") !!}</li>
                                @foreach ($users as $user)
                                <li class='dropdown-item'>
                                    <a href='javascript:void(0);' data-label='{{ sprintf(__("text.full_name"), $user["firstname"], $user['lastname']) }}' data-value='{{ $row['id'] }}_{{ $user['id'] }}_{{ $user['profileId'] }}' id='userStatusChange{{ $row['id'] }}{{ $user['id'] }}'>
                                        <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}' width='25' style='vertical-align: middle; margin-right:5px;' />{{ sprintf(__("text.full_name"), $user["firstname"], $user['lastname']) }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="pull-right" style="margin-right:10px;">
                            <a href="#/ideas/ideaDialog/{{ $row['id'] }}" class="" data="item_{{ $row['id'] }}" {{ $row['commentCount'] == 0 ? 'style="color: grey;"' : '' }}>
                                <span class="fas fa-comments"></span>
                            </a>
                            <small>{{ $row['commentCount'] }}</small>
                        </div>
                    </div>
                </div>

                @if ($row['milestoneHeadline'] != '')
                <br />
                <div hx-trigger="load" hx-indicator=".htmx-indicator" hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}">
                    <div class="htmx-indicator">
                        {!! __("label.loading_milestone") !!}
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @if (count($canvasItems) == 0)
        <div class='center'>
            <div style='width:30%' class='svgContainer'>
                {!! file_get_contents(ROOT . "/dist/images/svg/undraw_new_ideas_jdea.svg") !!}
            </div>
            <h3>{!! __("headlines.have_an_idea") !!}</h3><br />
            {!! __("subtitles.start_collecting_ideas") !!}<br /><br />
        </div>
        @endif
        <div class="clearfix"></div>
        @else
        <br /><br />
        <div class='center'>
            <div style='width:30%' class='svgContainer'>
                {!! file_get_contents(ROOT . "/dist/images/svg/undraw_new_ideas_jdea.svg") !!}
            </div>
            <h3>{!! __("headlines.have_an_idea") !!}</h3><br />
            {!! __("subtitles.start_collecting_ideas") !!}<br /><br />
            @if ($login::userIsAtLeast($roles::$editor))
            <a href="javascript:void(0)" class="addCanvasLink btn btn-primary">{!! __("links.icon.create_new_board") !!}</a>
            @endif
        </div>
        @endif

        <!-- Modals -->
        <div class="modal fade bs-example-modal-lg" id="addCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">{!! __("headlines.start_new_idea_board") !!}</h4>
                        </div>
                        <div class="modal-body">
                            <label>{!! __("label.topic_idea_board") !!}</label>
                            <input type="text" name="canvastitle" placeholder="{!! __(" input.placeholders.name_for_idea_board") !!}" style="width:90%" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{!! __("buttons.close") !!}</button>
                            <input type="submit" class="btn btn-default" value="{!! __(" buttons.create_board") !!}" name="newCanvas" />
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade bs-example-modal-lg" id="editCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">{!! __("headlines.edit_board_name") !!}</h4>
                        </div>
                        <div class="modal-body">
                            <label>{!! __("label.title_idea_board") !!}</label>
                            <input type="text" name="canvastitle" value="{{ $canvasTitle }}" style="width:90%" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{!! __("buttons.close") !!}</button>
                            <input type="submit" class="btn btn-default" value="{!! __(" buttons.save") !!}" name="editCanvas" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function() {
        //new SlimSelect({ select: '#searchCanvas' });

        leantime.ideasController.initMasonryWall();
        leantime.ideasController.initBoardControlModal();
        leantime.ideasController.initWallImageModals();

        @if ($login::userIsAtLeast($roles::$editor))
            leantime.ideasController.initStatusDropdown();
            leantime.ideasController.initUserDropdown();
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        @if (isset($_GET['showIdeaModal']))
            @php
                if ($_GET['showIdeaModal'] == "") {
                    $modalUrl = "&type=idea";
                } else {
                    $modalUrl = "/" . (int)$_GET['showIdeaModal'];
                }
            @endphp

            leantime.ideasController.openModalManually("{{ BASE_URL }}/ideas/ideaDialog{!! $modalUrl !!}");
            window.history.pushState({}, document.title, '{{ BASE_URL }}/ideas/showBoards');
        @endif
    });
</script>

@endsection