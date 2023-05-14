<div class="message-media"> 
 @if($message->type == 'standard')
  <p><strong>Message:</strong></p>
  <p>{!! $message->content !!}</p>
  <br>
 @elseif($message->type == '')
  <p><strong>Message title:</strong></p>
  <p>{!! $message->title !!}</p>
  <br>
  <p><strong>Message:</strong></p>
  <p>{!! $message->content !!}</p>
  <br>
 @elseif($message->type == 'multiple_choice')
  <p><strong>Message:</strong></p>
  <p>{!! $message->content !!}</p>
  <br>
  <?php $cnt = 1; ?>
  @foreach($message->questions as $question)
   <p> <strong>Question {{$cnt }}:</strong></p>
   {!! $question['question'] !!}
   <br>
   <br>
   <?php $cnt++; ?>
  @endforeach
 @elseif($message->type == 'survey')
  <p><strong>Message:</strong></p>
  <p>{!! $message->content !!}</p>
  <br>
  <?php $cnt = 1; ?>
  @foreach($message->surveys as $question)
   <p> <strong>Question {{$cnt }}:</strong></p>
   {!! $question['text'] !!}
   <br>
   <br>
   <?php $cnt++; ?>
  @endforeach  
 @endif
 @if($message->is_acknowledgement_required)
    <p><strong>Acknowledgement message:</strong></p>
    <p>{{ $message->acknowledgement_message }}</p>
    <br>
 @endif
 @if($message->getMedia()->count() > 0)
    <p><strong>Attachment:</strong></p>
    @foreach($message->getMedia() as $media)
        <p><a href="{{ $media->getUrl() }}" download>{{ $media->file_name }}</a></p>
    @endforeach
 @endif
</div>