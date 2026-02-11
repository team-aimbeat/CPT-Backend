@php
    $url = '';
    
    $MyNavBar = \Menu::make('MenuList', function ($menu) use($url){
        
        //Admin Dashboard
        $menu->add('<span class="item-name">'.__('message.dashboard').'</span>', ['route' => 'dashboard'])
            ->prepend('<i class="icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.5 15.5C13.5 13.6144 13.5 12.6716 14.0858 12.0858C14.6716 11.5 15.6144 11.5 17.5 11.5C19.3856 11.5 20.3284 11.5 20.9142 12.0858C21.5 12.6716 21.5 13.6144 21.5 15.5V17.5C21.5 19.3856 21.5 20.3284 20.9142 20.9142C20.3284 21.5 19.3856 21.5 17.5 21.5C15.6144 21.5 14.6716 21.5 14.0858 20.9142C13.5 20.3284 13.5 19.3856 13.5 17.5V15.5Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M2 8.5C2 10.3856 2 11.3284 2.58579 11.9142C3.17157 12.5 4.11438 12.5 6 12.5C7.88562 12.5 8.82843 12.5 9.41421 11.9142C10 11.3284 10 10.3856 10 8.5V6.5C10 4.61438 10 3.67157 9.41421 3.08579C8.82843 2.5 7.88562 2.5 6 2.5C4.11438 2.5 3.17157 2.5 2.58579 3.08579C2 3.67157 2 4.61438 2 6.5V8.5Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M13.5 5.5C13.5 4.56812 13.5 4.10218 13.6522 3.73463C13.8552 3.24458 14.2446 2.85523 14.7346 2.65224C15.1022 2.5 15.5681 2.5 16.5 2.5H18.5C19.4319 2.5 19.8978 2.5 20.2654 2.65224C20.7554 2.85523 21.1448 3.24458 21.3478 3.73463C21.5 4.10218 21.5 4.56812 21.5 5.5C21.5 6.43188 21.5 6.89782 21.3478 7.26537C21.1448 7.75542 20.7554 8.14477 20.2654 8.34776C19.8978 8.5 19.4319 8.5 18.5 8.5H16.5C15.5681 8.5 15.1022 8.5 14.7346 8.34776C14.2446 8.14477 13.8552 7.75542 13.6522 7.26537C13.5 6.89782 13.5 6.43188 13.5 5.5Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M2 18.5C2 19.4319 2 19.8978 2.15224 20.2654C2.35523 20.7554 2.74458 21.1448 3.23463 21.3478C3.60218 21.5 4.06812 21.5 5 21.5H7C7.93188 21.5 8.39782 21.5 8.76537 21.3478C9.25542 21.1448 9.64477 20.7554 9.84776 20.2654C10 19.8978 10 19.4319 10 18.5C10 17.5681 10 17.1022 9.84776 16.7346C9.64477 16.2446 9.25542 15.8552 8.76537 15.6522C8.39782 15.5 7.93188 15.5 7 15.5H5C4.06812 15.5 3.60218 15.5 3.23463 15.6522C2.74458 15.8552 2.35523 16.2446 2.15224 16.7346C2 17.1022 2 17.5681 2 18.5Z" stroke="currentColor" stroke-width="1.5"/>
                    </svg></i>')
            ->link->attr([ 'class' => activeRoute(route('dashboard')) ? 'nav-link active' : 'nav-link' ]);
            
        $menu->add('<span class="item-name">'.__('message.user').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="9" cy="6" r="4" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M15 9C16.6569 9 18 7.65685 18 6C18 4.34315 16.6569 3 15 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <ellipse cx="9" cy="17" rx="7" ry="4" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M18 14C19.7542 14.3847 21 15.3589 21 16.5C21 17.5293 19.9863 18.4229 18.5 18.8704" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg></i>')
            ->nickname('user')
            ->data('permission', 'user-list')
            ->link->attr(['class' => 'nav-link' ])
            ->href('#user');

            $menu->user->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.user')]).'</span>', ['route' => 'users.index'])
                ->data('permission', 'user-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('users.index')) ? 'nav-link active' : 'nav-link']);

            $menu->user->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.user')]).'</span>', ['route' => 'users.create'])
                ->data('permission', [ 'user-add', 'user-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')          
                ->link->attr(['class' => activeRoute(route('users.create')) || request()->is('users/*/edit') ? 'nav-link active' : 'nav-link']);

        $menu->add('<span class="item-name">'.__('message.injury').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-24" width="24"  viewBox="0 0 448 512">
                        <!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path fill="currentColor" d="M277.4 12C261.1 4.5 243.1 0 224 0c-53.7 0-99.5 33.1-118.5 80h81.2l90.7-68zM342.5 80c-7.9-19.5-20.7-36.2-36.5-49.5L240 80h102.5zM224 256c70.7 0 128-57.3 128-128 0-5.5-1-10.7-1.6-16H97.6c-.7 5.3-1.6 10.5-1.6 16 0 70.7 57.3 128 128 128zM80 299.7V512h128.3l-98.5-221.5A132.8 132.8 0 0 0 80 299.7zM0 464c0 26.5 21.5 48 48 48V320.2C18.9 344.9 0 381.3 0 422.4V464zm256-48h-55.4l42.7 96H256c26.5 0 48-21.5 48-48s-21.5-48-48-48zm57.6-128h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.7-5.8-72.9-16h-7.4l42.7 96H256c44.1 0 80 35.9 80 80 0 18.1-6.3 34.6-16.4 48H400c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z"/>
                        </svg>
                    </i>')
            ->nickname('injury')
            ->data('permission', 'injury-list')
            ->link->attr(['class' => 'nav-link' ])
            ->href('#injury');

            $menu->injury->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.injury')]).'</span>', ['route' => 'injury.index'])
                ->data('permission', 'injury-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('injury.index')) ? 'nav-link active' : 'nav-link']);

            $menu->injury->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.injury')]).'</span>', ['route' => 'injury.create'])
                ->data('permission', [ 'injury-add', 'injury-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('injury.create')) || request()->is('injury/*/edit') ? 'nav-link active' : 'nav-link']);


        $menu->add('<span class="item-name">'.__('message.sub_admin').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9.87651 15.2063C6.03251 15.2063 2.74951 15.7873 2.74951 18.1153C2.74951 20.4433 6.01251 21.0453 9.87651 21.0453C13.7215 21.0453 17.0035 20.4633 17.0035 18.1363C17.0035 15.8093 13.7415 15.2063 9.87651 15.2063Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9.8766 11.886C12.3996 11.886 14.4446 9.841 14.4446 7.318C14.4446 4.795 12.3996 2.75 9.8766 2.75C7.3546 2.75 5.3096 4.795 5.3096 7.318C5.3006 9.832 7.3306 11.877 9.8456 11.886H9.8766Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M19.2036 8.66919V12.6792" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M21.2497 10.6741H17.1597" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg></i>')
            ->nickname('sub_admin')
            ->data('permission', 'subadmin-list')
            ->link->attr(['class' => 'nav-link' ])
            ->href('#sub_admin');

            $menu->sub_admin->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.sub_admin')]).'</span>', ['route' => 'subadmin.index'])
                ->data('permission', 'subadmin-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('subadmin.index')) ? 'nav-link active' : 'nav-link']);

            $menu->sub_admin->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.sub_admin')]).'</span>', ['route' => 'subadmin.create'])
                ->data('permission', [ 'subadmin-add', 'subadmin-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')          
                ->link->attr(['class' => activeRoute(route('subadmin.create')) || request()->is('subadmin/*/edit') ? 'nav-link active' : 'nav-link']);

        $menu->add('<span class="item-name">'.__('message.equipment').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.32031 12.1982L12.2003 8.31823M15.3043 11.4222L11.4243 15.3023" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M3.43157 15.6193C2.52737 14.7151 2.07528 14.263 2.0108 13.7109C1.9964 13.5877 1.9964 13.4632 2.0108 13.3399C2.07528 12.7879 2.52737 12.3358 3.43156 11.4316C4.33575 10.5274 4.78785 10.0753 5.33994 10.0108C5.46318 9.9964 5.58768 9.9964 5.71092 10.0108C6.26301 10.0753 6.71511 10.5274 7.6193 11.4316L12.5684 16.3807C13.4726 17.2849 13.9247 17.737 13.9892 18.2891C14.0036 18.4123 14.0036 18.5368 13.9892 18.6601C13.9247 19.2122 13.4726 19.6642 12.5684 20.5684C11.6642 21.4726 11.2122 21.9247 10.6601 21.9892C10.5368 22.0036 10.4123 22.0036 10.2891 21.9892C9.73699 21.9247 9.28489 21.4726 8.3807 20.5684L3.43157 15.6193Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M11.4316 7.6193C10.5274 6.71511 10.0753 6.26301 10.0108 5.71092C9.9964 5.58768 9.9964 5.46318 10.0108 5.33994C10.0753 4.78785 10.5274 4.33576 11.4316 3.43156C12.3358 2.52737 12.7879 2.07528 13.3399 2.0108C13.4632 1.9964 13.5877 1.9964 13.7109 2.0108C14.263 2.07528 14.7151 2.52737 15.6193 3.43156L20.5684 8.3807C21.4726 9.28489 21.9247 9.73699 21.9892 10.2891C22.0036 10.4123 22.0036 10.5368 21.9892 10.6601C21.9247 11.2122 21.4726 11.6642 20.5684 12.5684C19.6642 13.4726 19.2122 13.9247 18.6601 13.9892C18.5368 14.0036 18.4123 14.0036 18.2891 13.9892C17.737 13.9247 17.2849 13.4726 16.3807 12.5684L11.4316 7.6193Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M18.0195 2.49805L21.1235 5.60206" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2.49609 18.0181L5.6001 21.1221" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg></i>')
            ->nickname('equipment')
            ->data('permission', 'equipment-list')
            ->link->attr(['class' => 'nav-link' ])
            ->href('#equipment');

            $menu->equipment->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.equipment')]).'</span>', ['route' => 'equipment.index'])
                ->data('permission', 'equipment-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('equipment.index')) ? 'nav-link active' : 'nav-link']);

            $menu->equipment->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.equipment')]).'</span>', ['route' => 'equipment.create'])
                ->data('permission', [ 'equipment-add', 'equipment-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('equipment.create')) || request()->is('equipment/*/edit') ? 'nav-link active' : 'nav-link']);

        $menu->add('<span class="item-name">'.__('message.exercise').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.1009 2.64567C14.1009 3.69258 13.2522 4.54134 12.2051 4.54134C11.1581 4.54134 10.3093 3.69258 10.3093 2.64567C10.3093 1.59876 11.1581 0.75 12.2051 0.75C13.2522 0.75 14.1009 1.59876 14.1009 2.64567Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M1.99902 8.50402V7.00612C1.99902 6.58326 2.3459 6.24263 2.76869 6.25032L12.3933 6.4253L22.2071 6.25007C22.6298 6.24252 22.9765 6.58311 22.9765 7.00587V8.50402C22.9765 8.92151 22.6381 9.25995 22.2206 9.25995H16.4162C15.9408 9.25995 15.5834 9.69361 15.6742 10.1603L17.9978 22.0998C18.0886 22.5664 17.7312 23.0001 17.2558 23.0001H16.2843C15.9478 23.0001 15.6518 22.7777 15.5582 22.4544L13.6852 15.9867C13.5916 15.6635 13.2956 15.4411 12.9591 15.4411H11.8413C11.4983 15.4411 11.1983 15.672 11.1105 16.0036L9.40821 22.4375C9.32047 22.7691 9.02045 23.0001 8.67743 23.0001H7.70562C7.23489 23.0001 6.87864 22.5745 6.96149 22.1111L9.10041 10.1489C9.18326 9.68555 8.82701 9.25995 8.35628 9.25995H2.75495C2.33746 9.25995 1.99902 8.92151 1.99902 8.50402Z" stroke="currentColor" stroke-width="1.5"/>
                    </svg></i>')
            ->nickname('exercise')
            ->data('permission', 'exercise-list')
            ->link->attr(['class' => 'nav-link' ])
            ->href('#exercise');

            $menu->exercise->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.exercise')]).'</span>', ['route' => 'exercise.index'])
                ->data('permission', 'exercise-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('exercise.index')) ? 'nav-link active' : 'nav-link']);

            $menu->exercise->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.exercise')]).'</span>', ['route' => 'exercise.create'])
                ->data('permission', [ 'exercise-add', 'exercise-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('exercise.create')) || request()->is('exercise/*/edit') ? 'nav-link active' : 'nav-link']);

            /*
            $menu->exercise->add('<span class="item-name">Video List</span>', ['route' => 'exercise.video.list'])
                ->data('permission', 'exercise-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('exercise.video.list')) ? 'nav-link active' : 'nav-link']);

            $menu->exercise->add('<span class="item-name">Add Video</span>', ['route' => 'exercise.video.create'])
                ->data('permission', [ 'exercise-add', 'exercise-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('exercise.video.create')) ? 'nav-link active' : 'nav-link']);
            */
        
        $menu->add('<span class="item-name">'.__('message.workout').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="4" r="2" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M11 16.0002V14.3667C11 13.8177 10.7561 13.297 10.3344 12.9455L9.33793 12.1152C8.61946 11.5164 8.57018 10.43 9.2315 9.76871L10.8855 8.11473C11.4193 7.5809 11.2452 6.67671 10.5513 6.37932C9.26627 5.82861 7.79304 5.94205 6.60752 6.68301L4.5 8.00021" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M7 14L6.67157 14.3284C6.09351 14.9065 5.80448 15.1955 5.43694 15.3478C5.0694 15.5 4.66065 15.5 3.84315 15.5H3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12.5 10H15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M19.4888 22.0001H3.08684C2.48659 22.0001 2 21.5135 2 20.9133C2 20.3853 2.37943 19.9337 2.89949 19.8427L19.0559 17.0153C20.5926 16.7464 22 17.9289 22 19.4889C22 20.8758 20.8757 22.0001 19.4888 22.0001Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M19.2916 8.88902L18.5499 8.77777L18.5499 8.77777L19.2916 8.88902ZM20.8773 7.22454L21.0244 7.95998L21.0244 7.95998L20.8773 7.22454ZM22.1471 7.73544C22.5533 7.6542 22.8167 7.25908 22.7354 6.85291C22.6542 6.44674 22.2591 6.18333 21.8529 6.26456L22.1471 7.73544ZM18.7417 17.6113L20.0333 9.00028L18.5499 8.77777L17.2583 17.3887L18.7417 17.6113ZM21.0244 7.95998L22.1471 7.73544L21.8529 6.26456L20.7302 6.48911L21.0244 7.95998ZM20.0333 9.00028C20.0862 8.64782 20.1178 8.44487 20.1568 8.2985C20.1744 8.23252 20.1885 8.19883 20.1965 8.18288C20.2002 8.17549 20.2024 8.17218 20.2029 8.17144C20.2034 8.17082 20.2034 8.17074 20.2037 8.17041L19.1177 7.13579C18.8906 7.37412 18.7782 7.64686 18.7076 7.91172C18.6418 8.15825 18.5978 8.45884 18.5499 8.77777L20.0333 9.00028ZM20.7302 6.48911C20.414 6.55235 20.1159 6.61086 19.8728 6.68852C19.6117 6.77197 19.3447 6.89746 19.1177 7.13579L20.2037 8.17041C20.2041 8.17009 20.2041 8.17 20.2047 8.16955C20.2054 8.169 20.2086 8.1666 20.2159 8.16256C20.2314 8.15385 20.2644 8.13813 20.3294 8.11734C20.4737 8.07123 20.6749 8.02987 21.0244 7.95998L20.7302 6.48911Z" fill="currentColor"/>
                    </svg></i>')
            ->nickname('workout')
            ->data('permission', 'workout-list')
            ->link->attr(['class' => 'nav-link' ])
            ->href('#workout');

            $menu->workout->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.workout')]).'</span>', ['route' => 'workout.index'])
                ->data('permission', 'workout-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('workout.index')) ? 'nav-link active' : 'nav-link']);

            $menu->workout->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.workout')]).'</span>', ['route' => 'workout.create'])
                ->data('permission', [ 'workout-add', 'workout-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('workout.create')) || request()->is('workout/*/edit') ? 'nav-link active' : 'nav-link']);

            $menu->workout->add('<span class="item-name">Warmup Videos</span>', ['route' => 'warmup.video.list'])
                ->data('permission', 'workout-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('warmup.video.list')) ? 'nav-link active' : 'nav-link']);

            $menu->workout->add('<span class="item-name">Add Warmup Video</span>', ['route' => 'warmup.video.create'])
                ->data('permission', [ 'workout-add', 'workout-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('warmup.video.create')) ? 'nav-link active' : 'nav-link']);

            $menu->workout->add('<span class="item-name">Stretching Videos</span>', ['route' => 'stretching.video.list'])
                ->data('permission', 'workout-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('stretching.video.list')) ? 'nav-link active' : 'nav-link']);

            $menu->workout->add('<span class="item-name">Add Stretching Video</span>', ['route' => 'stretching.video.create'])
                ->data('permission', [ 'workout-add', 'workout-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('stretching.video.create')) ? 'nav-link active' : 'nav-link']);
            
            $menu->workout->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.workouttype')]).'</span>', ['route' => 'workouttype.index'])
                ->data('permission', 'workouttype-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('workouttype.index')) ? 'nav-link active' : 'nav-link']);

        $menu->add('<span class="item-name">'.__('message.diet').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g>
                            <rect fill="none" height="24" width="24" />
                            <path d="M8.16,11c-1.43,0.07-3.52,0.57-4.54,2h6.55L8.16,11z" enable-background="new" opacity=".3" />
                            <path d="M1,21h15.01v0.98c0,0.56-0.45,1.01-1.01,1.01H2.01C1.45,22.99,1,22.54,1,21.98V21z M20.49,23.31L16,18.83V19H1v-2h13.17 l-2-2H1c0-3.24,2.46-5.17,5.38-5.79l-5.7-5.7L2.1,2.1L13,13l2,2l6.9,6.9L20.49,23.31z M10.17,13l-2-2c-1.42,0.06-3.52,0.56-4.55,2 H10.17z M23,5h-5V1h-2v4h-5l0.23,2h9.56l-1,9.97l1.83,1.83L23,5z" fill="currentColor"/>
                        </g>
                    </svg></i>')
            ->nickname('diet')
            ->data('permission', 'diet-list')
            ->link->attr(['class' => 'nav-link' ])
            ->href('#diet');

            $menu->diet->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.diet')]).'</span>', ['route' => 'diet.index'])
                ->data('permission', 'diet-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('diet.index')) ? 'nav-link active' : 'nav-link']);

            $menu->diet->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.diet')]).'</span>', ['route' => 'diet.create'])
                ->data('permission', [ 'diet-add', 'diet-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('diet.create')) || request()->is('diet/*/edit') ? 'nav-link active' : 'nav-link']);
            
            $menu->diet->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.categorydiet')]).'</span>', ['route' => 'categorydiet.index'])
                ->data('permission', 'categorydiet-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('categorydiet.index')) ? 'nav-link active' : 'nav-link']);
    
        $menu->add('<span class="item-name">'.__('message.level').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 22H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3 11C3 10.0572 3 9.58579 3.29289 9.29289C3.58579 9 4.05719 9 5 9C5.94281 9 6.41421 9 6.70711 9.29289C7 9.58579 7 10.0572 7 11V17C7 17.9428 7 18.4142 6.70711 18.7071C6.41421 19 5.94281 19 5 19C4.05719 19 3.58579 19 3.29289 18.7071C3 18.4142 3 17.9428 3 17V11Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M10 7C10 6.05719 10 5.58579 10.2929 5.29289C10.5858 5 11.0572 5 12 5C12.9428 5 13.4142 5 13.7071 5.29289C14 5.58579 14 6.05719 14 7V17C14 17.9428 14 18.4142 13.7071 18.7071C13.4142 19 12.9428 19 12 19C11.0572 19 10.5858 19 10.2929 18.7071C10 18.4142 10 17.9428 10 17V7Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M17 4C17 3.05719 17 2.58579 17.2929 2.29289C17.5858 2 18.0572 2 19 2C19.9428 2 20.4142 2 20.7071 2.29289C21 2.58579 21 3.05719 21 4V17C21 17.9428 21 18.4142 20.7071 18.7071C20.4142 19 19.9428 19 19 19C18.0572 19 17.5858 19 17.2929 18.7071C17 18.4142 17 17.9428 17 17V4Z" stroke="currentColor" stroke-width="1.5"/>
                    </svg></i>')
            ->nickname('level')
            ->data('permission', 'level-list')
            ->link->attr(['class' => 'nav-link' ])
            ->href('#level');

            $menu->level->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.level')]).'</span>', ['route' => 'level.index'])
                ->data('permission', 'level-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('level.index')) ? 'nav-link active' : 'nav-link']);

            $menu->level->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.level')]).'</span>', ['route' => 'level.create'])
                ->data('permission', [ 'level-add', 'level-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('level.create')) || request()->is('level/*/edit') ? 'nav-link active' : 'nav-link']);

        $menu->add('<span class="item-name">'.__('message.bodypart').'</span>', ['class' => ''])
                ->prepend('<i class="icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.38892 13.1614C8.22254 12.0779 12.9999 11.0891 16.6405 14.8096" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M2 10.5588C5.1153 10.2051 6.39428 10.6706 8.75105 12.2516M15.5021 13.7518L13.8893 6.66258C13.8186 6.35178 13.4791 6.18556 13.1902 6.32034L10.3819 7.6308C10.1384 7.74442 9.85788 7.75857 9.61117 7.65213C8.87435 7.33425 8.38405 6.97152 7.86685 6.31394C7.61986 5.99992 7.54201 5.5868 7.62818 5.19668C7.87265 4.0899 8.12814 3.34462 8.62323 2.31821C8.70119 2.15659 8.86221 2.05093 9.04141 2.04171C11.0466 1.93856 12.3251 2.01028 14.2625 2.44371C14.5804 2.51485 14.8662 2.69558 15.0722 2.948C19.8635 8.8193 21.3943 11.9968 21.9534 16.6216C21.9872 16.9004 21.8964 17.1818 21.7073 17.3895C17.6861 21.8064 14.7759 22.3704 8.75105 20.0604C6.65624 21.5587 5.07425 21.8624 2.25004 21.3106" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg></i>')
                ->nickname('bodypart')
                ->data('permission', 'bodyparts-list')
                ->link->attr(['class' => 'nav-link' ])
                ->href('#bodypart');

            $menu->bodypart->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.bodypart')]).'</span>', ['route' => 'bodypart.index'])
                ->data('permission', 'bodyparts-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('bodypart.index')) ? 'nav-link active' : 'nav-link']);

            $menu->bodypart->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.bodypart')]).'</span>', ['route' => 'bodypart.create'])
                ->data('permission', [ 'bodyparts-add', 'bodyparts-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('bodypart.create')) || request()->is('bodypart/*/edit') ? 'nav-link active' : 'nav-link']);

        // $menu->add('<span class="item-name">'.__('message.product').'</span>', ['class' => ''])
        //         ->prepend('<i class="icon">
        //                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        //                     <path d="M2 3L2.26491 3.0883C3.58495 3.52832 4.24497 3.74832 4.62248 4.2721C5 4.79587 5 5.49159 5 6.88304V9.5C5 12.3284 5 13.7426 5.87868 14.6213C6.75736 15.5 8.17157 15.5 11 15.5H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        //                     <path d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z" stroke="currentColor" stroke-width="1.5"/>
        //                     <path d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z" stroke="currentColor" stroke-width="1.5"/>
        //                     <path d="M5 6H16.4504C18.5054 6 19.5328 6 19.9775 6.67426C20.4221 7.34853 20.0173 8.29294 19.2078 10.1818L18.7792 11.1818C18.4013 12.0636 18.2123 12.5045 17.8366 12.7523C17.4609 13 16.9812 13 16.0218 13H5" stroke="currentColor" stroke-width="1.5"/>
        //                 </svg></i>')
        //         ->nickname('product')
        //         ->data('permission', 'product-list')
        //         ->link->attr(['class' => 'nav-link' ])
        //         ->href('#product');

        //     $menu->product->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.product')]).'</span>', ['route' => 'product.index'])
        //         ->data('permission', 'product-list')
        //         ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
        //         ->link->attr(['class' => activeRoute(route('product.index')) ? 'nav-link active' : 'nav-link']);

        //     $menu->product->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.product')]).'</span>', ['route' => 'product.create'])
        //         ->data('permission', [ 'product-add', 'product-edit'])
        //         ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
        //         ->link->attr(['class' => activeRoute(route('product.create')) || request()->is('product/*/edit') ? 'nav-link active' : 'nav-link']);
            
        //     $menu->product->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.productcategory')]).'</span>', ['route' => 'productcategory.index'])
        //         ->data('permission', 'productcategory-list')
        //         ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
        //         ->link->attr(['class' => activeRoute(route('productcategory.index')) ? 'nav-link active' : 'nav-link']);
            
        $menu->add('<span class="item-name">'.__('message.post').'</span>', ['class' => ''])
                ->prepend('<i class="icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.18 8.03933L18.6435 7.57589C19.4113 6.80804 20.6563 6.80804 21.4241 7.57589C22.192 8.34374 22.192 9.58868 21.4241 10.3565L20.9607 10.82M18.18 8.03933C18.18 8.03933 18.238 9.02414 19.1069 9.89309C19.9759 10.762 20.9607 10.82 20.9607 10.82M18.18 8.03933L13.9194 12.2999C13.6308 12.5885 13.4865 12.7328 13.3624 12.8919C13.2161 13.0796 13.0906 13.2827 12.9882 13.4975C12.9014 13.6797 12.8368 13.8732 12.7078 14.2604L12.2946 15.5L12.1609 15.901M20.9607 10.82L16.7001 15.0806C16.4115 15.3692 16.2672 15.5135 16.1081 15.6376C15.9204 15.7839 15.7173 15.9094 15.5025 16.0118C15.3203 16.0986 15.1268 16.1632 14.7396 16.2922L13.5 16.7054L13.099 16.8391M13.099 16.8391L12.6979 16.9728C12.5074 17.0363 12.2973 16.9867 12.1553 16.8447C12.0133 16.7027 11.9637 16.4926 12.0272 16.3021L12.1609 15.901M13.099 16.8391L12.1609 15.901" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M8 13H10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M8 9H14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M8 17H9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M19.8284 3.17157C18.6569 2 16.7712 2 13 2H11C7.22876 2 5.34315 2 4.17157 3.17157C3 4.34315 3 6.22876 3 10V14C3 17.7712 3 19.6569 4.17157 20.8284C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8284C20.7715 19.8853 20.9554 18.4796 20.9913 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg></i>')
                ->nickname('post')
                ->data('permission', 'post-list')
                ->link->attr(['class' => 'nav-link' ])
                ->href('#post');

            $menu->post->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.post')]).'</span>', ['route' => 'post.index'])
                ->data('permission', 'post-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('post.index')) ? 'nav-link active' : 'nav-link']);

            $menu->post->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.post')]).'</span>', ['route' => 'post.create'])
                ->data('permission', [ 'post-add', 'post-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('post.create')) || request()->is('post/*/edit') ? 'nav-link active' : 'nav-link']);

            $menu->post->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.category')]).'</span>', ['route' => 'category.index'])
                ->data('permission', 'category-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('category.index')) ? 'nav-link active' : 'nav-link']);
                
            $menu->post->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.tags')]).'</span>', ['route' => 'tags.index'])
                ->data('permission', 'tags-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('tags.index')) ? 'nav-link active' : 'nav-link']);
                
        $menu->add('<span class="item-name">'.__('message.package').'</span>', ['class' => ''])
                ->prepend('<i class="icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="4.75" y="1.75" width="14.5" height="20.5" rx="1.25" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M13.0896 6.50024L12.6588 5.17427C12.4514 4.53607 11.5486 4.53607 11.3412 5.17427L10.9104 6.50024H9.51615C8.84511 6.50024 8.56611 7.35893 9.10899 7.75336L10.2369 8.57286L9.80609 9.89883C9.59873 10.537 10.3292 11.0677 10.8721 10.6733L12 9.8538L13.1279 10.6733C13.6708 11.0677 14.4013 10.537 14.1939 9.89883L13.7631 8.57286L14.891 7.75336C15.4339 7.35893 15.1549 6.50024 14.4838 6.50024H13.0896Z" stroke="currentColor" stroke-width="1.14541"/>
                            <path d="M8 15.0215L16 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M8 19.0215L16 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        </i>')
                ->nickname('package')
                ->data('permission', 'package-list')
                ->link->attr(['class' => 'nav-link' ])
                ->href('#package');

            $menu->package->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.package')]).'</span>', ['route' => 'packages.index'])
                ->data('permission', 'package-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('packages.index')) ? 'nav-link active' : 'nav-link']);

            $menu->package->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.package')]).'</span>', ['route' => 'packages.create'])
                ->data('permission', [ 'package-add', 'package-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('packages.create')) || request()->is('package/*/edit') ? 'nav-link active' : 'nav-link']);

            $menu->package->add('<span class="item-name">Coupons</span>', ['route' => 'coupons.index'])
                ->data('permission', 'package-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('coupons.index')) ? 'nav-link active' : 'nav-link']);

            $menu->package->add('<span class="item-name">Add Coupon</span>', ['route' => 'coupons.create'])
                ->data('permission', 'package-add')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('coupons.create')) ? 'nav-link active' : 'nav-link']);

                
        if (false) {
        $menu->add('<span class="item-name">'.__('message.class_schedule').'</span>', ['class' => ''])
                ->prepend('<i class="icon">
                            <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.9951 16.6766V14.1396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M18.19 5.33008C19.88 5.33008 21.24 6.70008 21.24 8.39008V11.8301C18.78 13.2701 15.53 14.1401 11.99 14.1401C8.45 14.1401 5.21 13.2701 2.75 11.8301V8.38008C2.75 6.69008 4.12 5.33008 5.81 5.33008H18.19Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M15.4951 5.32576V4.95976C15.4951 3.73976 14.5051 2.74976 13.2851 2.74976H10.7051C9.48512 2.74976 8.49512 3.73976 8.49512 4.95976V5.32576" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M2.77441 15.4829L2.96341 17.9919C3.09141 19.6829 4.50041 20.9899 6.19541 20.9899H17.7944C19.4894 20.9899 20.8984 19.6829 21.0264 17.9919L21.2154 15.4829" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg> 
                        </i>')
                ->nickname('class_schedule')
                ->data('permission', 'class-schedule-list')
                ->link->attr(['class' => 'nav-link' ])
                ->href('#class_schedule');

            $menu->class_schedule->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.class_schedule')]).'</span>', ['route' => 'classschedule.index'])
                ->data('permission', 'class-schedule-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('classschedule.index')) ? 'nav-link active' : 'nav-link']);

            $menu->class_schedule->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.class_schedule')]).'</span>', ['route' => 'classschedule.create'])
                ->data('permission', [ 'class-schedule-add', 'class-schedule-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('classschedule.create')) || request()->is('classschedule/*/edit') ? 'nav-link active' : 'nav-link']);
        }

        $menu->add('<span class="item-name">'.__('message.subscription').'</span>', ['class' => ''])
                ->prepend('<i class="icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 20.3884H7.25993C8.27079 20.3884 9.29253 20.4937 10.2763 20.6964C12.0166 21.0549 13.8488 21.0983 15.6069 20.8138C16.4738 20.6734 17.326 20.4589 18.0975 20.0865C18.7939 19.7504 19.6469 19.2766 20.2199 18.7459C20.7921 18.216 21.388 17.3487 21.8109 16.6707C22.1736 16.0894 21.9982 15.3762 21.4245 14.943C20.7873 14.4619 19.8417 14.462 19.2046 14.9433L17.3974 16.3084C16.697 16.8375 15.932 17.3245 15.0206 17.4699C14.911 17.4874 14.7962 17.5033 14.6764 17.5172M14.6764 17.5172C14.6403 17.5214 14.6038 17.5254 14.5668 17.5292M14.6764 17.5172C14.8222 17.486 14.9669 17.396 15.1028 17.2775C15.746 16.7161 15.7866 15.77 15.2285 15.1431C15.0991 14.9977 14.9475 14.8764 14.7791 14.7759C11.9817 13.1074 7.62942 14.3782 5 16.2429M14.6764 17.5172C14.6399 17.525 14.6033 17.5292 14.5668 17.5292M14.5668 17.5292C14.0434 17.5829 13.4312 17.5968 12.7518 17.5326" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <rect x="2" y="14" width="3" height="8" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M11.1992 9H14.7992" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M18.7654 6.78078L18.9029 5.56316C19.0109 4.60685 19.0649 4.1287 18.8686 3.93104C18.7624 3.82412 18.618 3.7586 18.4636 3.7473C18.1782 3.72641 17.8198 4.06645 17.1029 4.74654C16.7321 5.09825 16.5468 5.2741 16.34 5.30134C16.2254 5.31643 16.1086 5.30091 16.0028 5.25654C15.8119 5.17646 15.6846 4.95906 15.43 4.52426L14.0878 2.23243C13.6067 1.41081 13.3661 1 13 1C12.6339 1 12.3933 1.41081 11.9122 2.23243L10.57 4.52426C10.3154 4.95906 10.1881 5.17646 9.99716 5.25654C9.89135 5.30091 9.77461 5.31643 9.66002 5.30134C9.45323 5.2741 9.26786 5.09825 8.89712 4.74654C8.18025 4.06645 7.82181 3.72641 7.53639 3.7473C7.38199 3.7586 7.23759 3.82412 7.13139 3.93104C6.93508 4.1287 6.98908 4.60685 7.09708 5.56316L7.2346 6.78078C7.46119 8.78708 7.57449 9.79024 8.28406 10.3951C8.99363 11 10.0571 11 12.184 11H13.816C15.9429 11 17.0064 11 17.7159 10.3951C18.4255 9.79024 18.5388 8.78708 18.7654 6.78078Z" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        </i>')
                ->nickname('subscription')
                ->data('permission', 'subscription-list')
                ->link->attr(['class' => 'nav-link' ])
                ->href('#subscription');

            $menu->subscription->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.subscription')]).'</span>', ['route' => 'subscription.index'])
                ->data('permission', 'subscription-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('subscription.index')) ? 'nav-link active' : 'nav-link']);

        // Account Setting menu hidden

        $menu->add('<span>'.__('message.pages').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.3116 12.6473L20.8293 10.7154C21.4335 8.46034 21.7356 7.3328 21.5081 6.35703C21.3285 5.58657 20.9244 4.88668 20.347 4.34587C19.6157 3.66095 18.4881 3.35883 16.2331 2.75458C13.978 2.15033 12.8504 1.84821 11.8747 2.07573C11.1042 2.25537 10.4043 2.65945 9.86351 3.23687C9.27709 3.86298 8.97128 4.77957 8.51621 6.44561C8.43979 6.7254 8.35915 7.02633 8.27227 7.35057L8.27222 7.35077L7.75458 9.28263C7.15033 11.5377 6.84821 12.6652 7.07573 13.641C7.25537 14.4115 7.65945 15.1114 8.23687 15.6522C8.96815 16.3371 10.0957 16.6392 12.3508 17.2435L12.3508 17.2435C14.3834 17.7881 15.4999 18.0873 16.415 17.9744C16.5152 17.9621 16.6129 17.9448 16.7092 17.9223C17.4796 17.7427 18.1795 17.3386 18.7203 16.7612C19.4052 16.0299 19.7074 14.9024 20.3116 12.6473Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M16.415 17.9741C16.2065 18.6126 15.8399 19.1902 15.347 19.6519C14.6157 20.3368 13.4881 20.6389 11.2331 21.2432C8.97798 21.8474 7.85044 22.1495 6.87466 21.922C6.10421 21.7424 5.40432 21.3383 4.86351 20.7609C4.17859 20.0296 3.87647 18.9021 3.27222 16.647L2.75458 14.7151C2.15033 12.46 1.84821 11.3325 2.07573 10.3567C2.25537 9.58627 2.65945 8.88638 3.23687 8.34557C3.96815 7.66065 5.09569 7.35853 7.35077 6.75428C7.77741 6.63996 8.16368 6.53646 8.51621 6.44531" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M11.7773 10L16.607 11.2941" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M11 12.8975L13.8978 13.6739" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg></i>')
            ->nickname('pages')
            ->data('permission', ['terms-condition','privacy-policy'])
            ->link->attr(['class' => 'nav-link' ])
            ->href('#pages');

            $menu->pages->add('<span>'.__('message.terms_condition').'</span>', ['route' => 'pages.term_condition'])
                ->data('permission', 'terms-condition')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('pages.term_condition')) ? 'nav-link active' : 'nav-link']);

            $menu->pages->add('<span>'.__('message.privacy_policy').'</span>', ['route' => 'pages.privacy_policy'])
                ->data('permission', 'privacy-policy')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('pages.privacy_policy')) ? 'nav-link active' : 'nav-link']);

        // Push Notification menu hidden
        // Quotes menu hidden

                $menu->add('<span class="item-name">'.__('message.faq').'</span>', ['class' => ''])
                ->prepend('<i class="icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="4.75" y="1.75" width="14.5" height="20.5" rx="1.25" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M13.0896 6.50024L12.6588 5.17427C12.4514 4.53607 11.5486 4.53607 11.3412 5.17427L10.9104 6.50024H9.51615C8.84511 6.50024 8.56611 7.35893 9.10899 7.75336L10.2369 8.57286L9.80609 9.89883C9.59873 10.537 10.3292 11.0677 10.8721 10.6733L12 9.8538L13.1279 10.6733C13.6708 11.0677 14.4013 10.537 14.1939 9.89883L13.7631 8.57286L14.891 7.75336C15.4339 7.35893 15.1549 6.50024 14.4838 6.50024H13.0896Z" stroke="currentColor" stroke-width="1.14541"/>
                            <path d="M8 15.0215L16 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M8 19.0215L16 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        </i>')
                ->nickname('faq')
                ->data('permission', 'faq-list')
                ->link->attr(['class' => 'nav-link' ])
                ->href('#faq');

            $menu->faq->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.faq')]).'</span>', ['route' => 'faqs.index'])
                ->data('permission', 'faq-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('faqs.index')) ? 'nav-link active' : 'nav-link']);

            $menu->faq->add('<span class="item-name">'.__('message.add_form_title',['form' => __('message.faq')]).'</span>', ['route' => 'faqs.create'])
                ->data('permission', [ 'faq-add', 'faq-edit'])
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('faqs.create')) || request()->is('faqs/*/edit') ? 'nav-link active' : 'nav-link']);

                



        $menu->add('<span class="item-name">'.__('message.app_language_setting').'</span>', ['class' => ''])
            ->prepend('<i class="icon">
                    <svg class="icon-32" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.8877 10.8967C19.2827 10.7007 20.3567 9.50473 20.3597 8.05573C20.3597 6.62773 19.3187 5.44373 17.9537 5.21973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M19.7285 14.2505C21.0795 14.4525 22.0225 14.9255 22.0225 15.9005C22.0225 16.5715 21.5785 17.0075 20.8605 17.2815" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.8867 14.6638C8.67273 14.6638 5.92773 15.1508 5.92773 17.0958C5.92773 19.0398 8.65573 19.5408 11.8867 19.5408C15.1007 19.5408 17.8447 19.0588 17.8447 17.1128C17.8447 15.1668 15.1177 14.6638 11.8867 14.6638Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.8869 11.888C13.9959 11.888 15.7059 10.179 15.7059 8.069C15.7059 5.96 13.9959 4.25 11.8869 4.25C9.7779 4.25 8.0679 5.96 8.0679 8.069C8.0599 10.171 9.7569 11.881 11.8589 11.888H11.8869Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M5.88509 10.8967C4.48909 10.7007 3.41609 9.50473 3.41309 8.05573C3.41309 6.62773 4.45409 5.44373 5.81909 5.21973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M4.044 14.2505C2.693 14.4525 1.75 14.9255 1.75 15.9005C1.75 16.5715 2.194 17.0075 2.912 17.2815" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg></i>')
            ->nickname('app_language_setting')
            ->data('permission', ['screen-list', 'defaultkeyword-list', 'languagelist-list', 'languagewithkeyword-list', 'bulkimport-add'])
            ->link->attr(['class' => 'nav-link' ])
            ->href('#app_language_setting');
                
            /*
            $menu->app_language_setting->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.screen')]).'</span>', ['route' => 'screen.index'])
                ->data('permission', 'screen-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('screen.index')) ? 'nav-link active' : 'nav-link']);

            $menu->app_language_setting->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.default_keyword')]).'</span>', ['route' => 'defaultkeyword.index'])
                ->data('permission', 'defaultkeyword-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('defaultkeyword.index')) ? 'nav-link active' : 'nav-link']);
            */

            $menu->app_language_setting->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.language')]).'</span>', ['route' => 'languagelist.index'])
                ->data('permission', 'languagelist-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => request()->is('languagelist') || request()->is('languagelist/*/edit') || request()->is('languagelist/create') ? 'nav-link active' : 'nav-link']);

            /*
            $menu->app_language_setting->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.language_keyword')]).'</span>', ['route' => 'languagewithkeyword.index'])
                ->data('permission', 'languagewithkeyword-list')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('languagewithkeyword.index')) ? 'nav-link active' : 'nav-link']);

            $menu->app_language_setting->add('<span class="item-name">'.__('message.list_form_title',['form' => __('message.bulk_import_langugage_data')]).'</span>', ['route' => 'bulk.language.data'])
                ->data('permission', 'bulkimport-add')
                ->prepend('<i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><g><circle cx="12" cy="12" r="8" fill="currentColor"></circle></g></svg></i>')
                ->link->attr(['class' => activeRoute(route('bulk.language.data')) ? 'nav-link active' : 'nav-link']);
            */
        
        $menu->add('<span class="item-name">'.__('message.setting').'</span>', ['route' => 'setting.index'])
            ->prepend('<i class="icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M13.7639 2.15224C13.3963 2 12.9304 2 11.9985 2C11.0666 2 10.6007 2 10.2332 2.15224C9.7431 2.35523 9.35375 2.74458 9.15076 3.23463C9.0581 3.45834 9.02184 3.7185 9.00765 4.09799C8.98679 4.65568 8.70079 5.17189 8.21748 5.45093C7.73417 5.72996 7.14412 5.71954 6.65073 5.45876C6.31498 5.2813 6.07154 5.18262 5.83147 5.15102C5.30558 5.08178 4.77372 5.22429 4.3529 5.5472C4.03728 5.78938 3.80431 6.1929 3.33837 6.99993C2.87243 7.80697 2.63946 8.21048 2.58753 8.60491C2.51829 9.1308 2.6608 9.66266 2.98371 10.0835C3.1311 10.2756 3.33824 10.437 3.65972 10.639C4.13233 10.936 4.43643 11.4419 4.43639 12C4.43636 12.5581 4.13228 13.0639 3.65972 13.3608C3.33818 13.5629 3.13101 13.7244 2.98361 13.9165C2.66071 14.3373 2.5182 14.8691 2.58743 15.395C2.63936 15.7894 2.87233 16.193 3.33827 17C3.80421 17.807 4.03718 18.2106 4.3528 18.4527C4.77362 18.7756 5.30548 18.9181 5.83137 18.8489C6.07143 18.8173 6.31486 18.7186 6.65057 18.5412C7.14401 18.2804 7.73409 18.27 8.21743 18.549C8.70077 18.8281 8.98679 19.3443 9.00765 19.9021C9.02184 20.2815 9.05811 20.5417 9.15076 20.7654C9.35375 21.2554 9.7431 21.6448 10.2332 21.8478C10.6007 22 11.0666 22 11.9985 22C12.9304 22 13.3963 22 13.7639 21.8478C14.2539 21.6448 14.6433 21.2554 14.8463 20.7654C14.9389 20.5417 14.9752 20.2815 14.9894 19.902C15.0103 19.3443 15.2962 18.8281 15.7795 18.549C16.2628 18.2699 16.853 18.2804 17.3464 18.5412C17.6821 18.7186 17.9255 18.8172 18.1656 18.8488C18.6915 18.9181 19.2233 18.7756 19.6442 18.4527C19.9598 18.2105 20.1927 17.807 20.6587 16.9999C21.1246 16.1929 21.3576 15.7894 21.4095 15.395C21.4788 14.8691 21.3362 14.3372 21.0133 13.9164C20.8659 13.7243 20.6588 13.5628 20.3373 13.3608C19.8647 13.0639 19.5606 12.558 19.5607 11.9999C19.5607 11.4418 19.8647 10.9361 20.3373 10.6392C20.6588 10.4371 20.866 10.2757 21.0134 10.0835C21.3363 9.66273 21.4789 9.13087 21.4096 8.60497C21.3577 8.21055 21.1247 7.80703 20.6588 7C20.1928 6.19297 19.9599 5.78945 19.6442 5.54727C19.2234 5.22436 18.6916 5.08185 18.1657 5.15109C17.9256 5.18269 17.6822 5.28136 17.3465 5.4588C16.853 5.71959 16.263 5.73002 15.7796 5.45096C15.2963 5.17191 15.0103 4.65566 14.9894 4.09794C14.9752 3.71848 14.9389 3.45833 14.8463 3.23463C14.6433 2.74458 14.2539 2.35523 13.7639 2.15224Z" stroke="currentColor" stroke-width="1.5"/>
                    </svg></i>')
            ->link->attr([ 'class' => activeRoute(route('setting.index')) ? 'nav-link active' : 'nav-link' ]);

        $menu->add('<span class="item-name">'.__('message.subscribers').'</span>', ['route' => 'subscribers.index'])
        ->data('permission', 'subscribers-list')
        ->prepend('<svg fill="currentColor" class="icon-24" width="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M96 0C60.7 0 32 28.7 32 64l0 384c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-384c0-35.3-28.7-64-64-64L96 0zM208 288l64 0c44.2 0 80 35.8 80 80c0 8.8-7.2 16-16 16l-192 0c-8.8 0-16-7.2-16-16c0-44.2 35.8-80 80-80zm-32-96a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM512 80c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64zM496 192c-8.8 0-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64c0-8.8-7.2-16-16-16zm16 144c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64z"/></svg>')
        ->link->attr([ 'class' => activeRoute(route('subscribers.index')) ? 'nav-link active' : 'nav-link' ]);
    })->filter(function ($item) {
        return checkMenuRoleAndPermission($item);
    });
@endphp
<ul class="navbar-nav iq-main-menu"  id="sidebar">
   
    
    <li><hr class="hr-horizontal"></li>
    
    @include(config('laravel-menu.views.bootstrap-items'), ['items' => $MyNavBar->roots()])
</ul>
