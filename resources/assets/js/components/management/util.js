import _ from 'lodash';
import Req from 'req';

export default {

    /**
     * Host group sorting function.
     * Sort groups so the host group is first.
     *
     * @param group
     * @returns {*}
     */
    hostGroupSortFunction(group){
        return group && group.group_name ?
            (!_.startsWith(group.group_name, 'host-')) + '_' + group.group_name :
            '';
    },

    /**
     * Sort groups so the host group is first.
     * Returns a new array.
     *
     * @param groups
     */
    sortHostGroups(groups){
        if (!groups || _.isEmpty(groups)){
            return groups;
        }

        return _.sortBy(groups, this.hostGroupSortFunction);
    },

    /**
     * Sorts host groups in place.
     * @param groups
     * @returns {*}
     */
    sortHostGroupsInPlace(groups){
        if (!groups || _.isEmpty(groups)){
            return groups;
        }

        return Req.sortByInPlace(groups, this.hostGroupSortFunction);
    },
}

