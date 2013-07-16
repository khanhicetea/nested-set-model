Nested Set Model
================

### Giới thiệu

![Chuyen muc da cap voi nest set model](https://dl.dropboxusercontent.com/u/120005387/songvidamme/2013/Jul/nested_set_model_1.png)

Bạn đang thiết kế 1 website hay 1 software có các chuyên mục đa cấp (có chuyên mục cha, con, cháu, chắt, chút, chít, v.v..). Làm sao để thiết kế CSDL để đạt sự tối ưu trong lưu trữ, truy vấn, chỉnh sửa đây ? Bài viết sẽ đưa ra 1 giải pháp hoàn toàn mới, và bạn sẽ thấy được tầm quan trọng của Cấu Trúc Dữ Liệu. 

### Các giải pháp truyền thống

- Sử dụng 1 field `parent_id` để lưu giá trị id của node cha ( parent_id = 0 khi node đó thuộc root - node 'ông tổ' ). Khi đó để lấy dữ liệu ra, phải dùng *đệ quy* để giải quyết => performance thấp.
- Sử dụng hệ thống multi-tables tương ứng với từng level. Hạn chế về số lượng level cố định, và số table nhiều, khó quản lý.

### Giải pháp mới : *Nested Set Model*

*Giải pháp mới này sử dụng cấu trúc cây dữ liệu như sau :*

![Chuyen muc da cap voi nest set model](https://dl.dropboxusercontent.com/u/120005387/songvidamme/2013/Jul/nested_set_model_2.png)

**Cây dữ liệu sẽ có những thuộc tính như sau :**

 1. Mỗi node ngoài giá trị `value` sẽ 2 giá trị là `left` và `right` ( `right` > `left` ). Ví dụ node A ( có left là 1, right là 8).
 2. Một node con cháu thuộc 1 node cha khi và chỉ khi `left con` > `left cha` AND `right con` < `right cha`. Ví dụ C là con của A ( 2 > 1 AND 5 < 8 ) và F là con của B ( 10 > 9 AND 11 < 12 ).
 3. Node *ông tổ* root có `left` = 0 và đường đi tăng dần sẽ theo "từ trên xuống dưới, từ trái qua phải". Xem hình minh họa bên dưới để hiểu rõ hơn đường đi.

 ![Chuyen muc da cap voi nest set model](https://dl.dropboxusercontent.com/u/120005387/songvidamme/2013/Jul/nested_set_model_3.gif)

 4. Các giá trị `left` và `right` của tất cả các node tạo thành 1 dãy số tự nhiên liên tục từ 0 đến (N*2 + 1) với N là tổng số node của cây ( không tính node root).


##### Xem tiếp phần 2 : [Thêm 1 node vào cây dữ liệu](http://songvidamme.com/chuyen-muc-da-cap-su-dung-nested-set-model---phan-2_p8.c)
