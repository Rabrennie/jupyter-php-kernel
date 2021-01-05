<?php

namespace JupyterPhpKernel\Responses;

use DateTime;
use Ramsey\Uuid\Uuid;

class Response
{
  const KERNEL_INFO_REPLY = 'kernel_info_reply';
  const STATUS = 'status';
  const EXECUTE_REPLY = 'execute_reply';
  const EXECUTE_RESULT = 'execute_result';

  protected string $type;
  protected array $content = [];
  protected array $header = [];
  protected array $parent_header = [];
  protected array $metadata = [];
  protected array $ids = [];

  public function __construct(string $type, string $session_id, array $content = [], array $parent_header = [], array $metadata = [], array $ids = [])
  {
    $this->type = $type;
    $this->content = $content;
    $this->parent_header = $parent_header;
    $this->metadata = $metadata;
    $this->ids = $ids;

    $this->header = [
      'date' => (new DateTime())->format('c'),
      'msg_id' => Uuid::uuid4()->toString(),
      'username' => 'kernel',
      'session' => $session_id,
      'msg_type' => $this->type,
      'version' => '5.3'
    ];
  }

  public function toMessage(string $key, string $signature_scheme)
  {
    $message = [
      json_encode((object)$this->header),
      json_encode((object)$this->parent_header),
      json_encode((object)$this->metadata),
      json_encode((object)$this->content)
    ];

    return [
      ...$this->ids,
      '<IDS|MSG>',
      $this->sign($message, $key, $signature_scheme),
      ...$message,
    ];
  }

  protected function sign(array $content, string $key, string $signature_scheme)
  {
    $hash_context = hash_init(str_replace('hmac-', '', $signature_scheme), HASH_HMAC, $key);
    
    foreach ($content as $data) {
      hash_update($hash_context, $data);
    }

    return hash_final($hash_context);
  }

}