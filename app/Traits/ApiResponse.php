<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponse
{
    // Define list Error code
    public $listErrorCode = [
        "ERROR_100" => "Validation failed",
        "ERROR_101" => "Số điện thoại hoặc mật khẩu không đúng",
        "ERROR_102" => "Mật khẩu không giống",
        "ERROR_103" => "Bạn đã nhập sai mã OTP",
        "ERROR_104" => "Bạn phải điền đầy đủ thông tin của bé",
        "ERROR_105" => "Số điện thoại không đúng",
        "ERROR_106" => "Nhập sai mật Khẩu",
        "ERROR_107" => "Vui lòng điền đầy đủ thông tin",
        "ERROR_108" => "Không tìm thấy tài khoản người dùng",
        "ERROR_109" => "Tài khoản người dùng chưa xác thực",
        "ERROR_110" => "Số điện thoại đã tồn tại, Vui Lòng đăng nhập",
        "ERROR_111" => "Địa chỉ email đã tồn tại",
        "ERROR_112" => "Cập nhật cài đặt không thành công",
        "ERROR_113" => "Không thể xác thực thông tin người dùng",
        "ERROR_114" => "Vui lòng cập nhật thông tin người dùng",
        "ERROR_115" => "Role không tồn tại",
        "ERROR_116" => "Type default avatar không đúng",
        "ERROR_117" => "Không tìm thấy avatar media",
        "ERROR_118" => "Tài khoản liên kết không hợp lệ.",
        "ERROR_119" => "Mật khẩu mới không giống",
        "ERROR_120" => "Lỗi! Không tìm thấy sự kiện",
        "ERROR_121" => "Số điện thoại này chưa có trong hệ thống",
        "ERROR_122" => "Mã lớp không tồn tại",
        "ERROR_123" => "Bạn đã thêm sự kiện này rồi",
        "ERROR_124" => "Số điện thoại đã được đăng ký với app hướng nghiệp. Vui đăng nhập/cập nhật thủ công hoặc tìm lại mật khẩu!",
        "ERROR_125" => "Không tồn tại câu hỏi",
        "ERROR_126" => "Không tồn tại bài test",
        "ERROR_127" => "không tìm thấy kết quả phù hợp",
        "ERROR_128" => "Trường birth year phải là thời gian bắt đầu trước hoặc đúng ngày hôm nay",
        "ERROR_129" => "Không đủ kết quả hiển thị",
        "ERROR_130" => "Tài khoản làm việc tốt đã được người dùng khác đăng ký vui lòng chọn số điện thoại khác!",
        "ERROR_131" => "Không tìm thấy khóa học",
        "ERROR_132" => "Bạn đã đăng ký khóa học này",
        "ERROR_133" => "Bạn chưa đăng ký khóa học này",
        "ERROR_134" => "Vui lòng chọn ít nhất 1 trường để tìm kiếm!",
        "ERROR_135" => "Dữ liệu đã được lưu nên không thể lưu lần nữa.",
        "ERROR_136" => "Nghề nghiệp đã lưu không tồn tại để xóa.",
        "ERROR_137" => "Bạn đang có mã OTP chờ xác thực, hãy thực hiện xác thực trước khi tạo mã OTP mới.",
        "ERROR_138" => "Bạn đã yêu thích khóa học này",
        "ERROR_139" => "Vui lòng chọn hình cho câu hỏi đầy đủ!",
        "ERROR_140" => "Câu hỏi này đã tồn tại !",
        "ERROR_141" => "Lỗi! không tìm thấy ngành.",
        "ERROR_142" => "Lỗi! không tìm thầy nghề nghiệp.",
        "ERROR_143" => "Không tồn tại đúng định dạng skills.",
        "ERROR_144" => "Không tồn tại đúng định dạng abilities.",
        "ERROR_145" => "Không tồn tại đúng định dạng personalities.",
        "ERROR_146" => "Không tồn tại đúng định dạng education.",
        "ERROR_147" => "Không tồn tại đúng định dạng salaries.",
        "ERROR_148" => "Sai định dạng JSON!",
        "ERROR_149" => "Nghề nghiệp đã tồn tại!",
        "ERROR_150" => "Vui lòng nhập tên ngành học!",
        "ERROR_151" => "Ngành học đã tồn tại!",
        "ERROR_152" => "Ngành học không tồn tại!",
        "ERROR_153" => "Vui lòng nhập tên trường học!",
        "ERROR_154" => "Trường học đã tồn tại!",
        "ERROR_155" => "Trường học không tồn tại!",
        "ERROR_156" => "Vui lòng nhập lương!",
        "ERROR_157" => "Tiền lương đã tồn tại!",
        "ERROR_158" => "Tiền lương không tồn tại!",
        "ERROR_159" => "Vui lòng nhập kĩ năng!",
        "ERROR_160" => "Kĩ năng đã tồn tại!",
        "ERROR_161" => "Kĩ năng không tồn tại!",
        "ERROR_162" => "Vui lòng nhập đầy đủ thông tin chuyên gia!",
        "ERROR_163" => "Chuyên gia này không tồn tại!",
        "ERROR_164" => "Bạn đã bỏ yêu thích khóa học này",
        "ERROR_165" => "Không tìm thấy lịch sử của bạn trong khóa học này",
        "ERROR_166" => "Bạn đã hoàn thành bài học này",
        "ERROR_167" => "Không tìm thấy nhóm tính cách!",
        "ERROR_168" => "Tạo status không thành công!",
        "ERROR_169" => "Bài đăng này không tồn tại!",
        "ERROR_170" => "Địa chỉ email này chưa có trong hệ thống",
        "ERROR_171" => "Call video vẫn chưa bắt đầu, không thể kết thúc!",
        "ERROR_172" => "User này không phải là thành viên phòng chat này!",
        "ERROR_173" => "Vẫn còn người trong phòng call, không thể kết thúc cuộc gọi!",
        "ERROR_174" => "Người dùng này không phải chủ phòng chat khộng thể kick người dùng khác!",
        "ERROR_175" => "Đang bắt máy không thể bắt máy lần nữa!",
        "ERROR_176" => "Tạo phòng chat không thành công!",
        "ERROR_177" => "Người dùng đã tồn tại trong nhóm!",
        "ERROR_178" => "Phòng chat không tồn tại!",
        "ERROR_179" => "Không thể nhắn tin với bản thân!",
        "ERROR_180" => "Không thể gửi tin nhắn trống!",
        "ERROR_181" => "Gửi yêu cầu kết bạn không thành công!",
        "ERROR_182" => "Tài khoản hoặc mật khẩu admin không đúng!",
        "ERROR_183" => "Trạng thái này không tồn tại!",
        "ERROR_184" => "Bài đăng này đã được duyệt, không thể duyệt lần nữa!",
        "ERROR_185" => "2 người đã có mối quan hệ, không thể gửi hoặc hủy yêu cầu kết bạn lần nữa!",
        "ERROR_186" => "Không thể tạo quan hệ với chính bản thân",
        "ERROR_187" => "Người dùng chưa gửi yêu cầu kết bạn nên không thể hủy yêu cầu!",
        "ERROR_188" => "Người dùng đã là bạn bè trước đó!",
        "ERROR_189" => "Không ai gửi yêu cầu kết bạn để chấp thuận!",
        "ERROR_190" => "Người dùng vẫn chưa có bạn bè",
        "ERROR_191" => "Call video thất bại!",
        "ERROR_192" => "Video call không tồn tại!",
        "ERROR_193" => "Không thể gửi tin nhắn!",
        "ERROR_194" => "Phòng vẫn chưa được gọi, không thể bắt máy!",
        "ERROR_195" => "Không thể tự kick bản thân!",
        "ERROR_196" => "Người dùng cần trao quyền chủ room cho người khác mới rời nhóm được!",
        "ERROR_197" => "Cần phải truyền người dùng vào!",
        "ERROR_198" => "Người dùng không phải là chủ phòng!",
        "ERROR_199" => "Không thể cấp quyền cho bản thân!",
        "ERROR_200" => "Cuộc gọi vẫn chưa diễn ra!",
        "ERROR_201" => "Có người khác đã gọi nên không thể tiến hành cuộc gọi!",
    ];


    public $listNotification = [
        "NOTI_100" => "Bé Của Bạn Vừa Hoàn Thành việc Tốt",
        "NOTI_101" => "Chúc mừng bé đã làm được 5 việc tốt",
        "NOTI_102" => "Chúc mừng bé đã làm được 10 việc tốt",
        "NOTI_103" => "Chúc mừng [KID_NAME] đã đạt cấp [LEVEL_UP]",
        "NOTI_104" => "Nhập thành công!",
        "NOTI_105" => "Sửa thành công!",
        "NOTI_106" => "Xóa thành công!",
        "NOTI_107" => "Đã đăng thành công!",
        "NOTI_108" => "Sửa bài viết thành công!",
        "NOTI_109" => "Duyệt bài viết thành công!",
        "NOTI_110" => "Tạo phòng chat thành công!",
        "NOTI_111" => "Thêm thành viên vào phòng chat thành công!",
        "NOTI_116" => "Tin nhắn đã được gửi đi!",
        "NOTI_117" => "Đã gửi yêu cầu kết bạn!",
        "NOTI_118" => "Đã hủy yêu cầu kết bạn!",
        "NOTI_119" => "Kết bạn thành công!",
        "NOTI_120" => "Không chấp nhận kết bạn!",
        "NOTI_121" => "Call video thành công!",
        "NOTI_122" => "Nhấc máy thành công!",
        "NOTI_123" => "Video Call đã bị từ chối!",
        "NOTI_124" => "Đã vào quá trình call video!",
        "NOTI_125" => "Video call đã kết thúc!",
        "NOTI_126" => "Ngắt cuộc gọi thành công!",
        "NOTI_127" => "Từ chối cuộc gọi thành công!",
        "NOTI_128" => "Thông báo tin nhắn đã được gửi đi!",
        "NOTI_129" => "Người dùng đã xem tin nhắn !",
    ];

    /**
     * Build success response
     * @param string|array $data
     * @param int $code
     * @param string $message
     * @return JsonResponse
     */
    public function successResponse($data, $code = Response::HTTP_OK, $message = "Success"): JsonResponse
    {
        return response()->json(
            ['data' => $data, 'message' => $message],
            $code,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
            JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Build error responses
     * @param string|array $message
     * @param $httpRespondCode
     * @param null $errorCode
     * @return JsonResponse
     */
    public function errorResponse($message, $httpRespondCode, $errorCode = null)
    {
        $errors = [];
        $validationMessage = $this->getMessageError("ERROR_100");
        if (empty($errorCode)) {
            $errorCode = "ERROR_100";
        }

        if (is_string($message)) {
            $validationMessage = $message;
        } else {
            $errors = $message;
        }

        return response()->json([
            'code' => $httpRespondCode,
            "message" => $validationMessage,
            'error_type' => $errorCode,
            'errors' => $errors,
        ], $httpRespondCode,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Build error message responses
     * @param int $errorCode
     * @return string
     */
    public function getMessageError($errorCode = null): string
    {
        if (isset($this->listErrorCode[$errorCode])) {
            return $this->listErrorCode[$errorCode];
        }

        return "";
    }

    /**
     * Build notification message responses
     * @param int $notiCode
     * @return String
     */
    public function getMessageNoti($notiCode = null): string
    {
        if (isset($this->listNotification[$notiCode])) {
            return $this->listNotification[$notiCode];
        }
        return "";
    }
}