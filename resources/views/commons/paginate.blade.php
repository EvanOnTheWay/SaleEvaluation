<div class="dataTables_wrapper dt-bootstrap4 no-footer">
    <div class="row" style="margin-top: 15px;">
        <div class="col-sm-12 col-md-5">
            <div class="dataTables_info">
            @if ($items->hasPages())
                <span>本页显示 {{ $items->firstItem() }} - {{ $items->lastItem() }}, 共 {{ $items->total() }} 条记录</span>
            @endif
        </div>
        </div>

        <div class="col-sm-12 col-md-7">
            <div class="dataTables_paginate paging_simple_numbers">
            <ul class="pagination">
                <li class="paginate_button page-item"><a href="{{ $items->url(1) }}" class="page-link">首页</a></li>
                <!-- Previous Page Link -->
                @if ($items->onFirstPage())
                    <li class="paginate_button page-item previous disabled"><a href="#" class="page-link">上一页</a>
                    </li>
                @else
                    <li class="paginate_button page-item previous"><a href="{{ $items->previousPageUrl() }}"
                                                                      class="page-link">上一页</a></li>
                @endif

            <!-- Pagination Elements -->
                @foreach ($elements as $element)
                <!-- "Three Dots" Separator -->
                    @if (is_string($element))
                        <li class="disabled"><span>{{ $element }}</span></li>
                    @endif

                <!-- Array Of Links -->
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $items->currentPage())
                                <li class="paginate_button page-item active"><span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="paginate_button page-item"><a class="page-link"
                                                                         href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

            <!-- Next Page Link -->
                @if ($items->hasMorePages())
                    <li class="paginate_button page-item next"><a href="{{ $items->nextPageUrl() }}" class="page-link">下一页</a>
                    </li>
                @else
                    <li class="paginate_button page-item next disabled"><a href="#" class="page-link">下一页</a></li>
                @endif
                <li class="paginate_button page-item"><a href="{{ $items->url($items->lastPage()) }}" class="page-link">尾页</a></li>
            </ul>
            </div>
        </div>
    </div>


</div>
