<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\County;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Ohio cities data: county seats and cities over 10,000 population
     * Format: [county_slug, city_name, population, lat, lng, is_major, is_county_seat]
     */
    protected array $cities = [
        // Adams County
        ['adams', 'West Union', 3200, 38.7945, -83.5460, false, true],

        // Allen County
        ['allen', 'Lima', 36015, 40.7428, -84.1053, true, true],
        ['allen', 'Delphos', 7042, 40.8434, -84.3391, false, false],

        // Ashland County
        ['ashland', 'Ashland', 20485, 40.8687, -82.3182, true, true],

        // Ashtabula County
        ['ashtabula', 'Ashtabula', 18167, 41.8651, -80.7898, true, true],
        ['ashtabula', 'Conneaut', 12530, 41.9476, -80.5548, true, false],
        ['ashtabula', 'Geneva', 6215, 41.8051, -80.9481, false, false],

        // Athens County
        ['athens', 'Athens', 24726, 39.3292, -82.1013, true, true],

        // Auglaize County
        ['auglaize', 'Wapakoneta', 9820, 40.5678, -84.1936, false, true],
        ['auglaize', 'St. Marys', 8346, 40.5423, -84.3894, false, false],

        // Belmont County
        ['belmont', 'St. Clairsville', 5765, 40.0801, -80.9001, false, true],
        ['belmont', 'Martins Ferry', 6713, 40.0959, -80.7245, false, false],

        // Brown County
        ['brown', 'Georgetown', 4586, 38.8645, -83.9041, false, true],

        // Butler County
        ['butler', 'Hamilton', 62082, 39.3995, -84.5613, true, true],
        ['butler', 'Middletown', 48765, 39.5151, -84.3983, true, false],
        ['butler', 'Fairfield', 42510, 39.3301, -84.5604, true, false],
        ['butler', 'West Chester', 64420, 39.3350, -84.4133, true, false],
        ['butler', 'Liberty Township', 37798, 39.3356, -84.3980, true, false],
        ['butler', 'Oxford', 22912, 39.5070, -84.7453, true, false],
        ['butler', 'Monroe', 14801, 39.4401, -84.3619, true, false],
        ['butler', 'Trenton', 13191, 39.4812, -84.4577, true, false],

        // Carroll County
        ['carroll', 'Carrollton', 3217, 40.5726, -81.0857, false, true],

        // Champaign County
        ['champaign', 'Urbana', 11328, 40.1084, -83.7527, true, true],

        // Clark County
        ['clark', 'Springfield', 58662, 39.9242, -83.8088, true, true],
        ['clark', 'New Carlisle', 5765, 39.9362, -84.0255, false, false],

        // Clermont County
        ['clermont', 'Batavia', 1548, 39.0770, -84.1769, false, true],
        ['clermont', 'Milford', 6825, 39.1751, -84.2944, false, false],
        ['clermont', 'Loveland', 13227, 39.2698, -84.2638, true, false],
        ['clermont', 'Amelia', 5065, 39.0284, -84.2180, false, false],

        // Clinton County
        ['clinton', 'Wilmington', 12520, 39.4453, -83.8285, true, true],

        // Columbiana County
        ['columbiana', 'Lisbon', 2739, 40.7720, -80.7676, false, true],
        ['columbiana', 'Salem', 12098, 40.9009, -80.8568, true, false],
        ['columbiana', 'East Liverpool', 10195, 40.6187, -80.5720, true, false],
        ['columbiana', 'Columbiana', 6467, 40.8873, -80.6937, false, false],

        // Coshocton County
        ['coshocton', 'Coshocton', 11216, 40.2720, -81.8596, true, true],

        // Crawford County
        ['crawford', 'Bucyrus', 11683, 40.8084, -82.9755, true, true],
        ['crawford', 'Galion', 10114, 40.7337, -82.7899, true, false],

        // Cuyahoga County
        ['cuyahoga', 'Cleveland', 372624, 41.4993, -81.6944, true, true],
        ['cuyahoga', 'Parma', 78103, 41.4048, -81.7229, true, false],
        ['cuyahoga', 'Lakewood', 50249, 41.4819, -81.7982, true, false],
        ['cuyahoga', 'Euclid', 46710, 41.5931, -81.5268, true, false],
        ['cuyahoga', 'Cleveland Heights', 44186, 41.5200, -81.5631, true, false],
        ['cuyahoga', 'Strongsville', 44108, 41.3145, -81.8357, true, false],
        ['cuyahoga', 'Westlake', 31719, 41.4553, -81.9179, true, false],
        ['cuyahoga', 'North Olmsted', 31567, 41.4156, -81.9235, true, false],
        ['cuyahoga', 'Shaker Heights', 27947, 41.4739, -81.5370, true, false],
        ['cuyahoga', 'Garfield Heights', 27129, 41.4170, -81.6065, true, false],
        ['cuyahoga', 'Maple Heights', 22178, 41.4095, -81.5657, true, false],
        ['cuyahoga', 'South Euclid', 21091, 41.5234, -81.5243, true, false],
        ['cuyahoga', 'North Royalton', 30585, 41.3137, -81.7246, true, false],
        ['cuyahoga', 'Parma Heights', 19633, 41.3901, -81.7604, true, false],
        ['cuyahoga', 'Solon', 23800, 41.3898, -81.4410, true, false],
        ['cuyahoga', 'Brunswick', 34560, 41.2384, -81.8418, true, false],
        ['cuyahoga', 'Broadview Heights', 19214, 41.3137, -81.6854, true, false],
        ['cuyahoga', 'Rocky River', 19901, 41.4756, -81.8389, true, false],
        ['cuyahoga', 'Bay Village', 15068, 41.4848, -81.9222, true, false],
        ['cuyahoga', 'Fairview Park', 16063, 41.4420, -81.8640, true, false],
        ['cuyahoga', 'Brooklyn', 10775, 41.4395, -81.7351, true, false],
        ['cuyahoga', 'Bedford', 12623, 41.3931, -81.5365, true, false],
        ['cuyahoga', 'Warrensville Heights', 13033, 41.4351, -81.5362, true, false],
        ['cuyahoga', 'University Heights', 12904, 41.4981, -81.5368, true, false],
        ['cuyahoga', 'Richmond Heights', 10474, 41.5545, -81.5082, true, false],
        ['cuyahoga', 'Middleburg Heights', 15542, 41.3612, -81.8126, true, false],
        ['cuyahoga', 'Seven Hills', 11598, 41.3820, -81.6760, true, false],

        // Darke County
        ['darke', 'Greenville', 13084, 40.1028, -84.6330, true, true],

        // Defiance County
        ['defiance', 'Defiance', 16494, 41.2845, -84.3558, true, true],

        // Delaware County
        ['delaware', 'Delaware', 41302, 40.2987, -83.0680, true, true],
        ['delaware', 'Powell', 13353, 40.1579, -83.0752, true, false],
        ['delaware', 'Westerville', 41103, 40.1262, -82.9291, true, false],
        ['delaware', 'Sunbury', 6328, 40.2426, -82.8591, false, false],

        // Erie County
        ['erie', 'Sandusky', 24651, 41.4489, -82.7079, true, true],
        ['erie', 'Huron', 6931, 41.3950, -82.5552, false, false],
        ['erie', 'Vermilion', 10559, 41.4220, -82.3646, true, false],

        // Fairfield County
        ['fairfield', 'Lancaster', 40336, 39.7137, -82.5993, true, true],
        ['fairfield', 'Pickerington', 22158, 39.8842, -82.7535, true, false],
        ['fairfield', 'Canal Winchester', 9035, 39.8423, -82.8046, false, false],

        // Fayette County
        ['fayette', 'Washington Court House', 14204, 39.5362, -83.4391, true, true],

        // Franklin County
        ['franklin', 'Columbus', 905748, 39.9612, -82.9988, true, true],
        ['franklin', 'Dublin', 49328, 40.0992, -83.1141, true, false],
        ['franklin', 'Westerville', 41103, 40.1262, -82.9291, true, false],
        ['franklin', 'Grove City', 41820, 39.8812, -83.0930, true, false],
        ['franklin', 'Gahanna', 35442, 40.0192, -82.8791, true, false],
        ['franklin', 'Reynoldsburg', 38969, 39.9551, -82.8121, true, false],
        ['franklin', 'Hilliard', 36534, 40.0334, -83.1588, true, false],
        ['franklin', 'Upper Arlington', 35223, 39.9945, -83.0624, true, false],
        ['franklin', 'Whitehall', 18807, 39.9667, -82.8855, true, false],
        ['franklin', 'Worthington', 14897, 40.0931, -83.0180, true, false],
        ['franklin', 'Bexley', 13849, 39.9687, -82.9377, true, false],
        ['franklin', 'New Albany', 11699, 40.0812, -82.8088, true, false],
        ['franklin', 'Grandview Heights', 8251, 39.9812, -83.0405, false, false],
        ['franklin', 'Groveport', 5363, 39.8637, -82.8835, false, false],

        // Fulton County
        ['fulton', 'Wauseon', 7163, 41.5492, -84.1416, false, true],
        ['fulton', 'Archbold', 4584, 41.5212, -84.3072, false, false],
        ['fulton', 'Swanton', 3651, 41.5884, -83.8908, false, false],

        // Gallia County
        ['gallia', 'Gallipolis', 3172, 38.8101, -82.2018, false, true],

        // Geauga County
        ['geauga', 'Chardon', 5148, 41.5812, -81.2080, false, true],
        ['geauga', 'South Russell', 4094, 41.4312, -81.3599, false, false],

        // Greene County
        ['greene', 'Xenia', 26947, 39.6845, -83.9296, true, true],
        ['greene', 'Beavercreek', 47741, 39.7092, -84.0633, true, false],
        ['greene', 'Fairborn', 34622, 39.8209, -84.0194, true, false],
        ['greene', 'Bellbrook', 7277, 39.6337, -84.0786, false, false],
        ['greene', 'Yellow Springs', 3669, 39.8067, -83.8869, false, false],
        ['greene', 'Cedarville', 4284, 39.7451, -83.8086, false, false],

        // Guernsey County
        ['guernsey', 'Cambridge', 10635, 40.0312, -81.5885, true, true],

        // Hamilton County
        ['hamilton', 'Cincinnati', 309317, 39.1031, -84.5120, true, true],
        ['hamilton', 'Forest Park', 18720, 39.2901, -84.5041, true, false],
        ['hamilton', 'Sharonville', 13560, 39.2684, -84.4133, true, false],
        ['hamilton', 'Blue Ash', 12417, 39.2320, -84.3783, true, false],
        ['hamilton', 'Norwood', 19883, 39.1556, -84.4597, true, false],
        ['hamilton', 'Reading', 10383, 39.2237, -84.4422, true, false],
        ['hamilton', 'Springdale', 11223, 39.2873, -84.4852, true, false],
        ['hamilton', 'Deer Park', 5736, 39.2051, -84.3944, false, false],
        ['hamilton', 'Madeira', 9193, 39.1909, -84.3636, false, false],
        ['hamilton', 'Montgomery', 10251, 39.2284, -84.3544, true, false],
        ['hamilton', 'Wyoming', 8540, 39.2312, -84.4633, false, false],
        ['hamilton', 'Indian Hill', 5979, 39.1773, -84.3355, false, false],
        ['hamilton', 'North College Hill', 9318, 39.2173, -84.5505, false, false],
        ['hamilton', 'Finneytown', 12711, 39.2006, -84.5202, true, false],
        ['hamilton', 'White Oak', 19167, 39.2101, -84.5994, true, false],
        ['hamilton', 'Bridgetown', 13570, 39.1545, -84.6355, true, false],
        ['hamilton', 'Cheviot', 8375, 39.1573, -84.6133, false, false],
        ['hamilton', 'Loveland', 13227, 39.2698, -84.2638, true, false],
        ['hamilton', 'Mason', 33964, 39.3601, -84.3101, true, false],

        // Hancock County
        ['hancock', 'Findlay', 41512, 41.0442, -83.6499, true, true],

        // Hardin County
        ['hardin', 'Kenton', 8262, 40.6467, -83.6099, false, true],

        // Harrison County
        ['harrison', 'Cadiz', 3293, 40.2726, -80.9973, false, true],

        // Henry County
        ['henry', 'Napoleon', 8749, 41.3923, -84.1252, false, true],

        // Highland County
        ['highland', 'Hillsboro', 6572, 39.2026, -83.6116, false, true],

        // Hocking County
        ['hocking', 'Logan', 6704, 39.5401, -82.4077, false, true],

        // Holmes County
        ['holmes', 'Millersburg', 3025, 40.5548, -81.9180, false, true],

        // Huron County
        ['huron', 'Norwalk', 17194, 41.2426, -82.6158, true, true],
        ['huron', 'Willard', 6028, 41.0526, -82.7263, false, false],
        ['huron', 'Bellevue', 8048, 41.2737, -82.8424, false, false],

        // Jackson County
        ['jackson', 'Jackson', 6270, 39.0520, -82.6366, false, true],

        // Jefferson County
        ['jefferson', 'Steubenville', 18161, 40.3698, -80.6340, true, true],
        ['jefferson', 'Toronto', 5091, 40.4645, -80.6009, false, false],
        ['jefferson', 'Wintersville', 3977, 40.3762, -80.7040, false, false],

        // Knox County
        ['knox', 'Mount Vernon', 16926, 40.3934, -82.4857, true, true],

        // Lake County
        ['lake', 'Painesville', 19817, 41.7245, -81.2457, true, true],
        ['lake', 'Mentor', 46722, 41.6661, -81.3396, true, false],
        ['lake', 'Eastlake', 18089, 41.6537, -81.4504, true, false],
        ['lake', 'Willoughby', 22835, 41.6395, -81.4065, true, false],
        ['lake', 'Wickliffe', 12622, 41.6051, -81.4682, true, false],
        ['lake', 'Willowick', 14121, 41.6320, -81.4682, true, false],
        ['lake', 'Mentor-on-the-Lake', 7445, 41.7073, -81.3599, false, false],
        ['lake', 'Kirtland', 6892, 41.5987, -81.3613, false, false],

        // Lawrence County
        ['lawrence', 'Ironton', 10749, 38.5365, -82.6824, true, true],

        // Licking County
        ['licking', 'Newark', 49934, 40.0581, -82.4013, true, true],
        ['licking', 'Heath', 10624, 40.0231, -82.4460, true, false],
        ['licking', 'Pataskala', 16259, 39.9959, -82.6744, true, false],
        ['licking', 'Granville', 5646, 40.0681, -82.5194, false, false],

        // Logan County
        ['logan', 'Bellefontaine', 13370, 40.3612, -83.7594, true, true],

        // Lorain County
        ['lorain', 'Elyria', 53757, 41.3684, -82.1076, true, true],
        ['lorain', 'Lorain', 64028, 41.4528, -82.1824, true, false],
        ['lorain', 'North Ridgeville', 34012, 41.3892, -82.0190, true, false],
        ['lorain', 'Avon', 24040, 41.4517, -82.0354, true, false],
        ['lorain', 'Avon Lake', 24040, 41.5053, -82.0282, true, false],
        ['lorain', 'Amherst', 12572, 41.3978, -82.2224, true, false],
        ['lorain', 'Oberlin', 8286, 41.2940, -82.2171, false, false],
        ['lorain', 'Sheffield Lake', 9068, 41.4873, -82.1010, false, false],
        ['lorain', 'Grafton', 6636, 41.2728, -82.0552, false, false],
        ['lorain', 'Wellington', 4802, 41.1670, -82.2177, false, false],

        // Lucas County
        ['lucas', 'Toledo', 270871, 41.6528, -83.5379, true, true],
        ['lucas', 'Oregon', 20016, 41.6437, -83.4327, true, false],
        ['lucas', 'Sylvania', 19328, 41.7187, -83.7127, true, false],
        ['lucas', 'Maumee', 13832, 41.5628, -83.6538, true, false],
        ['lucas', 'Perrysburg', 21895, 41.5570, -83.6271, true, false],
        ['lucas', 'Holland', 1806, 41.6209, -83.7113, false, false],
        ['lucas', 'Waterville', 5523, 41.5009, -83.7180, false, false],

        // Madison County
        ['madison', 'London', 10158, 39.8865, -83.4483, true, true],

        // Mahoning County
        ['mahoning', 'Youngstown', 60068, 41.0998, -80.6495, true, true],
        ['mahoning', 'Boardman', 34648, 41.0284, -80.6668, true, false],
        ['mahoning', 'Austintown', 29677, 41.1001, -80.7612, true, false],
        ['mahoning', 'Canfield', 7515, 41.0251, -80.7612, false, false],
        ['mahoning', 'Struthers', 10282, 41.0526, -80.6076, true, false],
        ['mahoning', 'Poland', 2579, 41.0240, -80.6151, false, false],
        ['mahoning', 'Campbell', 7841, 41.0795, -80.5993, false, false],

        // Marion County
        ['marion', 'Marion', 35757, 40.5887, -83.1285, true, true],

        // Medina County
        ['medina', 'Medina', 26678, 41.1384, -81.8637, true, true],
        ['medina', 'Wadsworth', 23927, 41.0259, -81.7299, true, false],
        ['medina', 'Brunswick', 34560, 41.2384, -81.8418, true, false],

        // Meigs County
        ['meigs', 'Pomeroy', 1641, 39.0276, -82.0330, false, true],

        // Mercer County
        ['mercer', 'Celina', 10333, 40.5487, -84.5702, true, true],
        ['mercer', 'Coldwater', 4686, 40.4798, -84.6286, false, false],

        // Miami County
        ['miami', 'Troy', 26203, 40.0395, -84.2033, true, true],
        ['miami', 'Piqua', 21159, 40.1448, -84.2424, true, false],
        ['miami', 'Tipp City', 10039, 39.9584, -84.1724, true, false],
        ['miami', 'Covington', 2559, 40.1173, -84.3541, false, false],

        // Monroe County
        ['monroe', 'Woodsfield', 2362, 39.7618, -81.1149, false, true],

        // Montgomery County
        ['montgomery', 'Dayton', 137644, 39.7589, -84.1916, true, true],
        ['montgomery', 'Kettering', 55103, 39.6895, -84.1688, true, false],
        ['montgomery', 'Huber Heights', 37217, 39.8498, -84.1246, true, false],
        ['montgomery', 'Centerville', 23882, 39.6284, -84.1594, true, false],
        ['montgomery', 'Trotwood', 24231, 39.7973, -84.3116, true, false],
        ['montgomery', 'Vandalia', 14896, 39.8906, -84.1988, true, false],
        ['montgomery', 'Miamisburg', 20020, 39.6426, -84.2866, true, false],
        ['montgomery', 'West Carrollton', 12929, 39.6726, -84.2527, true, false],
        ['montgomery', 'Riverside', 25127, 39.7784, -84.1241, true, false],
        ['montgomery', 'Englewood', 13465, 39.8773, -84.3024, true, false],
        ['montgomery', 'Clayton', 13201, 39.8634, -84.3594, true, false],
        ['montgomery', 'Brookville', 5884, 39.8373, -84.4124, false, false],
        ['montgomery', 'Moraine', 6307, 39.7073, -84.2194, false, false],
        ['montgomery', 'Oakwood', 8936, 39.7251, -84.1741, false, false],

        // Morgan County
        ['morgan', 'McConnelsville', 1768, 39.6487, -81.8524, false, true],

        // Morrow County
        ['morrow', 'Mount Gilead', 3660, 40.5498, -82.8271, false, true],

        // Muskingum County
        ['muskingum', 'Zanesville', 25378, 39.9401, -82.0132, true, true],

        // Noble County
        ['noble', 'Caldwell', 1748, 39.7482, -81.5166, false, true],

        // Ottawa County
        ['ottawa', 'Port Clinton', 5992, 41.5120, -82.9377, false, true],
        ['ottawa', 'Marblehead', 845, 41.5370, -82.7127, false, false],

        // Paulding County
        ['paulding', 'Paulding', 3599, 41.1384, -84.5808, false, true],

        // Perry County
        ['perry', 'New Lexington', 4707, 39.7137, -82.2088, false, true],

        // Pickaway County
        ['pickaway', 'Circleville', 14021, 39.6001, -82.9460, true, true],

        // Pike County
        ['pike', 'Waverly', 4408, 39.1262, -83.0005, false, true],

        // Portage County
        ['portage', 'Ravenna', 11337, 41.1576, -81.2421, true, true],
        ['portage', 'Kent', 29698, 41.1537, -81.3579, true, false],
        ['portage', 'Streetsboro', 16312, 41.2392, -81.3457, true, false],
        ['portage', 'Aurora', 16029, 41.3176, -81.3454, true, false],
        ['portage', 'Stow', 34837, 41.1595, -81.4404, true, false],

        // Preble County
        ['preble', 'Eaton', 8328, 39.7437, -84.6366, false, true],

        // Putnam County
        ['putnam', 'Ottawa', 4456, 41.0195, -84.0469, false, true],

        // Richland County
        ['richland', 'Mansfield', 46454, 40.7589, -82.5155, true, true],
        ['richland', 'Ontario', 6234, 40.7598, -82.5899, false, false],
        ['richland', 'Shelby', 9026, 40.8815, -82.6616, false, false],
        ['richland', 'Lexington', 4822, 40.6784, -82.5824, false, false],

        // Ross County
        ['ross', 'Chillicothe', 21901, 39.3331, -82.9824, true, true],

        // Sandusky County
        ['sandusky', 'Fremont', 16015, 41.3503, -83.1219, true, true],
        ['sandusky', 'Clyde', 6106, 41.3042, -82.9752, false, false],

        // Scioto County
        ['scioto', 'Portsmouth', 20226, 38.7318, -82.9977, true, true],

        // Seneca County
        ['seneca', 'Tiffin', 17408, 41.1145, -83.1780, true, true],
        ['seneca', 'Fostoria', 13071, 41.1570, -83.4169, true, false],

        // Shelby County
        ['shelby', 'Sidney', 20534, 40.2845, -84.1555, true, true],

        // Stark County
        ['stark', 'Canton', 70447, 40.7989, -81.3784, true, true],
        ['stark', 'Massillon', 32192, 40.7967, -81.5215, true, false],
        ['stark', 'Alliance', 22322, 40.9153, -81.1062, true, false],
        ['stark', 'North Canton', 17034, 40.8759, -81.4024, true, false],
        ['stark', 'Louisville', 9058, 40.8373, -81.2593, false, false],
        ['stark', 'Canal Fulton', 5479, 40.8898, -81.5976, false, false],

        // Summit County
        ['summit', 'Akron', 190469, 41.0814, -81.5190, true, true],
        ['summit', 'Cuyahoga Falls', 48920, 41.1340, -81.4846, true, false],
        ['summit', 'Stow', 34837, 41.1595, -81.4404, true, false],
        ['summit', 'Barberton', 25589, 41.0128, -81.6051, true, false],
        ['summit', 'Green', 27393, 40.9484, -81.4762, true, false],
        ['summit', 'Twinsburg', 18795, 41.3126, -81.4401, true, false],
        ['summit', 'Tallmadge', 17537, 41.1012, -81.4218, true, false],
        ['summit', 'Hudson', 22262, 41.2401, -81.4407, true, false],
        ['summit', 'Fairlawn', 7437, 41.1287, -81.6099, false, false],
        ['summit', 'Macedonia', 11795, 41.3137, -81.5082, true, false],
        ['summit', 'Mogadore', 3807, 41.0465, -81.3976, false, false],
        ['summit', 'Norton', 11913, 41.0287, -81.6382, true, false],
        ['summit', 'Munroe Falls', 5012, 41.1431, -81.4357, false, false],
        ['summit', 'Silver Lake', 2519, 41.1576, -81.4551, false, false],

        // Trumbull County
        ['trumbull', 'Warren', 38955, 41.2373, -80.8184, true, true],
        ['trumbull', 'Niles', 18443, 41.1826, -80.7654, true, false],
        ['trumbull', 'Girard', 9540, 41.1540, -80.7012, false, false],
        ['trumbull', 'Hubbard', 7877, 41.1576, -80.5693, false, false],
        ['trumbull', 'Cortland', 7104, 41.3301, -80.7254, false, false],
        ['trumbull', 'Howland', 17546, 41.2512, -80.7460, true, false],
        ['trumbull', 'Champion', 8856, 41.2948, -80.8590, false, false],

        // Tuscarawas County
        ['tuscarawas', 'New Philadelphia', 17288, 40.4898, -81.4457, true, true],
        ['tuscarawas', 'Dover', 12826, 40.5201, -81.4740, true, false],
        ['tuscarawas', 'Uhrichsville', 5202, 40.3926, -81.3463, false, false],

        // Union County
        ['union', 'Marysville', 24848, 40.2365, -83.3671, true, true],

        // Van Wert County
        ['van-wert', 'Van Wert', 10633, 40.8695, -84.5841, true, true],

        // Vinton County
        ['vinton', 'McArthur', 1675, 39.2462, -82.4780, false, true],

        // Warren County
        ['warren', 'Lebanon', 20781, 39.4351, -84.2030, true, true],
        ['warren', 'Mason', 33964, 39.3601, -84.3101, true, false],
        ['warren', 'Springboro', 18633, 39.5523, -84.2333, true, false],
        ['warren', 'Franklin', 11771, 39.5573, -84.3041, true, false],
        ['warren', 'Deerfield Township', 38635, 39.4448, -84.2833, true, false],
        ['warren', 'South Lebanon', 5654, 39.3701, -84.2133, false, false],

        // Washington County
        ['washington', 'Marietta', 13303, 39.4151, -81.4549, true, true],
        ['washington', 'Belpre', 6441, 39.2740, -81.5724, false, false],

        // Wayne County
        ['wayne', 'Wooster', 26724, 40.8051, -81.9351, true, true],
        ['wayne', 'Orrville', 8305, 40.8370, -81.7640, false, false],
        ['wayne', 'Rittman', 6491, 40.9784, -81.7821, false, false],

        // Williams County
        ['williams', 'Bryan', 8545, 41.4748, -84.5524, false, true],
        ['williams', 'Montpelier', 4002, 41.5848, -84.6058, false, false],

        // Wood County
        ['wood', 'Bowling Green', 31820, 41.3748, -83.6513, true, true],
        ['wood', 'Perrysburg', 21895, 41.5570, -83.6271, true, false],
        ['wood', 'Rossford', 6293, 41.6098, -83.5644, false, false],
        ['wood', 'Northwood', 5265, 41.6017, -83.4844, false, false],

        // Wyandot County
        ['wyandot', 'Upper Sandusky', 6596, 40.8270, -83.2816, false, true],
    ];

    public function run(): void
    {
        $this->command->info('Seeding Ohio cities...');

        // Get all county IDs mapped by slug
        $counties = County::pluck('id', 'slug')->toArray();

        $created = 0;
        $skipped = 0;

        foreach ($this->cities as $cityData) {
            [$countySlug, $name, $population, $lat, $lng, $isMajor, $isCountySeat] = $cityData;

            $countyId = $counties[$countySlug] ?? null;

            if (!$countyId) {
                $this->command->warn("County not found: {$countySlug}");
                continue;
            }

            $countyName = Str::ucfirst($countySlug);

            $slug = Str::slug($name);

            // Check if city slug already exists (globally unique)
            $exists = City::where('slug', $slug)->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            City::create([
                'county_id' => $countyId,
                'name' => $name,
                'slug' => $slug,
                'description' => $isCountySeat
                    ? "{$name} is the county seat of {$countyName} County, Ohio."
                    : "{$name} is a city in {$countyName} County, Ohio.",
                'meta_title' => "{$name}, Ohio - Local Guide & Community",
                'meta_description' => "Explore {$name}, Ohio. Find local businesses, events, and community information.",
                'is_major' => $isMajor,
                'coordinates' => ['lat' => $lat, 'lng' => $lng],
                'population' => $population,
                'demographics' => null,
                'incorporated_year' => null,
            ]);

            $created++;
        }

        $this->command->info("Created {$created} cities, skipped {$skipped} existing cities.");
    }
}
