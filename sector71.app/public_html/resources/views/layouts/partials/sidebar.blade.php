<!-- Left side column. contains the logo and sidebar -->
<aside class="side-bar tw-relative tw-hidden tw-h-full tw-bg-white tw-w-64 xl:tw-w-64 lg:tw-flex lg:tw-flex-col tw-shrink-0">

    <!-- sidebar: style can be found in sidebar.less -->

    {{-- <a href="{{route('home')}}" class="logo">
<span class="logo-lg">{{ Session::get('business.name') }}</span>
</a> --}}

    <a href="{{route('home')}}"
        class="tw-flex tw-items-center tw-justify-center tw-w-full tw-border-r tw-h-15 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-shrink-0 tw-border-primary-500/30">
        <p class="tw-text-lg tw-font-medium tw-text-white side-bar-heading tw-text-center">
            {{ Session::get('business.name') }} <span class="tw-inline-block tw-w-3 tw-h-3 tw-bg-green-400 tw-rounded-full" title="Online"></span>
        </p>
    </a>

    <div class="tw-px-3 tw-pt-3 tw-border-r tw-border-gray-200">
        <input
            id="admin-sidebar-search"
            type="text"
            class="tw-w-full tw-rounded-lg tw-border tw-border-gray-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
            placeholder="Search menu...">
    </div>

    <!-- Sidebar Menu -->
    {!! Menu::render('admin-sidebar-menu', 'adminltecustom') !!}

    <script>
        (function () {
            function initSidebarSearch() {
                var input = document.getElementById('admin-sidebar-search');
                var sideBar = document.getElementById('side-bar');
                if (!input || !sideBar) {
                    return;
                }

                var topLevelItems = Array.prototype.slice.call(sideBar.children || []);

                topLevelItems.forEach(function (item) {
                    var childContainer = item.querySelector('.chiled');
                    if (childContainer) {
                        childContainer.setAttribute('data-default-display', childContainer.style.display || '');
                    }
                });

                input.addEventListener('input', function () {
                    var query = (input.value || '').toLowerCase().trim();

                    topLevelItems.forEach(function (item) {
                        var childContainer = item.querySelector('.chiled');
                        var childLinks = childContainer ? Array.prototype.slice.call(childContainer.querySelectorAll('a')) : [];
                        var toggle = item.querySelector('a.drop_down');
                        var itemText = ((toggle ? toggle.textContent : item.textContent) || '').toLowerCase();

                        if (!query) {
                            item.style.display = '';
                            if (childContainer) {
                                childContainer.style.display = childContainer.getAttribute('data-default-display') || '';
                                childLinks.forEach(function (link) {
                                    link.style.display = '';
                                });
                            }

                            return;
                        }

                        if (childLinks.length) {
                            var visibleChildren = 0;

                            childLinks.forEach(function (link) {
                                var childMatch = (link.textContent || '').toLowerCase().indexOf(query) !== -1;
                                link.style.display = childMatch ? '' : 'none';
                                if (childMatch) {
                                    visibleChildren++;
                                }
                            });

                            var parentMatch = itemText.indexOf(query) !== -1;
                            var shouldShow = parentMatch || visibleChildren > 0;
                            item.style.display = shouldShow ? '' : 'none';
                            childContainer.style.display = shouldShow ? 'block' : 'none';
                        } else {
                            item.style.display = itemText.indexOf(query) !== -1 ? '' : 'none';
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSidebarSearch);
            } else {
                initSidebarSearch();
            }
        })();
    </script>

    <!-- /.sidebar-menu -->
    <!-- /.sidebar -->
</aside>
