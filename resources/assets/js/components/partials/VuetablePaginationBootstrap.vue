<template>
    <ul v-if="tablePagination && tablePagination.last_page > 1" :class="css.wrapperClass">
        <li :class="{'disabled': isOnFirstPage}">
            <a href="" @click.prevent="loadPage('prev')">
                <span>&laquo;</span>
            </a>
        </li>

        <template v-if="notEnoughPages">
            <li v-for="n in totalPage" :class="{'active': isCurrentPage(n)}">
                <a @click.prevent="loadPage(n)" v-html="n"></a>
            </li>
        </template>
        <template v-else>
            <li v-if="!firstPageVisible">
                <a @click.prevent="loadPage(1)" v-html="1"></a>
            </li>
            <li v-if="show10ShiftLeft">
                <a @click.prevent="loadPage(shift10Left)" v-html="shift10Left"></a>
            </li>
            <li v-if="show100ShiftLeft">
                <a @click.prevent="loadPage(shift100Left)" v-html="shift100Left"></a>
            </li>

            <li v-for="n in windowSize" :class="{'active': isCurrentPage(windowStart+n-1)}">
                <a @click.prevent="loadPage(windowStart+n-1)" v-html="windowStart+n-1"></a>
            </li>

            <li v-if="show10ShiftRight">
                <a @click.prevent="loadPage(shift10Right)" v-html="shift10Right"></a>
            </li>
            <li v-if="show100ShiftRight">
                <a @click.prevent="loadPage(shift100Right)" v-html="shift100Right"></a>
            </li>
            <li v-if="!lastPageVisible">
                <a @click.prevent="loadPage(lastPage)" v-html="lastPage"></a>
            </li>
        </template>

        <li :class="{'disabled': isOnLastPage}">
            <a href="" @click.prevent="loadPage('next')">
                <span>&raquo;</span>
            </a>
        </li>
    </ul>
</template>

<script>
    import VuetablePaginationMixin from 'vuetable-2/src/components/VuetablePaginationMixin'
    export default {
        mixins: [VuetablePaginationMixin],
        computed: {
            lastPage(){
                return this.tablePagination.last_page;
            },
            firstPageVisible(){
                return this.windowStart === 1;
            },
            lastPageVisible(){
                return (this.windowStart + this.windowSize) >= this.totalPage+1;
            },
            show10Shift(){
                return this.totalPage >= 20;
            },
            shift10Left(){
                return Math.max(1, this.tablePagination.current_page - this.onEachSide - 10);
            },
            shift10Right(){
                return Math.min(this.totalPage, this.tablePagination.current_page + this.onEachSide + 10);
            },
            show10ShiftLeft(){
                return this.show10Shift && !this.firstPageVisible && this.shift10Left !== 1;
            },
            show10ShiftRight(){
                return this.show10Shift && !this.lastPageVisible && this.shift10Right !== this.lastPage;
            },

            show100Shift(){
                return this.totalPage >= 200;
            },
            shift100Left(){
                return Math.max(1, this.tablePagination.current_page - this.onEachSide - 100);
            },
            shift100Right(){
                return Math.min(this.totalPage, this.tablePagination.current_page + this.onEachSide + 100);
            },
            show100ShiftLeft(){
                return this.show100Shift && !this.firstPageVisible && this.shift100Left !== 1;
            },
            show100ShiftRight(){
                return this.show100Shift && !this.lastPageVisible && this.shift100Right !== this.lastPage;
            },

            numExtraFieldsShown(){
                return !this.firstPageVisible + !this.lastPageVisible
                    + this.show10ShiftLeft + this.show10ShiftRight
                    + this.show100ShiftLeft + this.show100ShiftRight;
            },

            maxExtraFieldsShown(){
                return 2 + 2*(!!this.show10Shift) + 2*(!!this.show100Shift);
            },
        }
    }
</script>

